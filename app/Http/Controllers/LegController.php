<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourLeg;
use App\Models\TourLegUser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class LegController extends Controller
{
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leg' => ['required', 'exists:tour_legs,id'],
            'date' => ['required', 'array'],
            'date.calendar.identifier' => ['required', 'string', 'in:gregory'],
            'date.era' => ['required', 'string', 'in:AD'],
            'date.year' => ['required', 'integer'],
            'date.month' => ['required', 'integer', 'between:1,12'],
            'date.day' => ['required', 'integer', 'between:1,31'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $d = $request->input('date');
            $date = Carbon::create($d['year'] ?? 0, $d['month'] ?? 0, $d['day'] ?? 0);
            if ($date->isToday() || $date->isFuture()) {
                $validator->errors()->add('date', 'The selected date must be before today.');
            }
        });

        $validated = $validator->validate();

        $data = $validated['date'];
        $date = Carbon::create($data['year'], $data['month'], $data['day']);

        $from = $date->copy()->subHours(24)->startOfDay()->format('Y-m-d H:i');
        $to = $date->copy()->endOfDay()->format('Y-m-d H:i');

        $vatsimId = Auth::user()->id;
        $apiUrl = 'https://api.statsim.net/api/Flights/VatsimId';
        $query = http_build_query([
            'vatsimId' => $vatsimId,
            'from' => $from,
            'to' => $to,
        ]);
        $url = "$apiUrl?$query";

        try {
            $response = Http::acceptJson()->get($url);
        } catch (ConnectionException) {
            return Inertia::flash('checkResult', [
                'leg_id' => $validated['leg'],
                'status' => 'error',
                'message' => 'Unable to connect to flight service Statsim.',
            ])->back();
        }

        if (! in_array($response->status(), [200, 404])) {
            return Inertia::flash('checkResult', [
                'leg_id' => $validated['leg'],
                'status' => 'error',
                'message' => 'Flight service Statsim returned an unexpected error.',
            ])->back();
        }

        $leg = TourLeg::findOrFail($validated['leg']);
        $tour = Tour::findOrFail($leg->tour_id);
        $leg_user = TourLegUser::where('tour_leg_id', $leg->id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        if ($leg_user->completed_at !== null) {
            return Inertia::flash('checkResult', [
                'leg_id' => $validated['leg'],
                'status' => 'error',
                'message' => 'This leg is already completed.',
            ])->back();
        }

        $flights = collect(! empty($response->json()) ? $response->json() : [])
            ->map(fn ($flight) => (object) $flight);
        $flight_results = self::check_flights($flights, $tour, $leg);

        $chosenFlight = $flight_results->first(fn ($flight) => $flight->all_valid);
        if ($chosenFlight) {
            $leg_user->completed_at = $chosenFlight->time_to_enter;
            $leg_user->save();
        }

        if ($flight_results->isEmpty()) {
            return Inertia::flash('checkResult', [
                'leg_id' => $validated['leg'],
                'status' => 'no_flights_found',
            ])->back();
        }

        return Inertia::flash('checkResult', [
            'leg_id' => $validated['leg'],
            'status' => 'found',
            'selected_flight' => $chosenFlight,
            'all_flights' => $flight_results->values(),
            'completed_at' => $leg_user->completed_at,
        ])->back();
    }

    private static function check_flights(Collection $flights, Tour $tour, TourLeg $leg): Collection
    {
        $current_aircrafts = explode(',', $tour->aircraft);
        $current_start_time = $tour->begins_at;
        $current_end_time = $tour->ends_at;
        if ($tour->require_order) {
            $all_legs = $tour->legs;
            foreach ($all_legs as $l) {
                $ul = TourLegUser::where('tour_leg_id', $l->id)->where('user_id', Auth::user()->id)->firstOrFail();
                if ($ul->completed_at !== null) {
                    $current_start_time = $ul->completed_at;
                }
            }
        }

        return $flights->map(function ($flight) use ($tour, $leg, $current_aircrafts, $current_end_time, $current_start_time) {

            $results = [];

            // Departure check
            $departureValid = strtoupper($flight->departure) === strtoupper($leg->departure_icao);
            $results[] = (object) [
                'check' => 'departure',
                'expected' => strtoupper($leg->departure_icao),
                'actual' => strtoupper($flight->departure),
                'valid' => $departureValid,
            ];

            // Destination check
            $destinationValid = strtoupper($flight->destination) === strtoupper($leg->arrival_icao);
            $results[] = (object) [
                'check' => 'destination',
                'expected' => strtoupper($leg->arrival_icao),
                'actual' => strtoupper($flight->destination),
                'valid' => $destinationValid,
            ];

            // Aircraft check (starts-with)
            $matchFound = false;
            foreach ($current_aircrafts as $type) {
                if (str_starts_with($flight->aircraft, $type)) {
                    $matchFound = true;
                    break;
                }
            }
            $aircraftValid = empty($tour->aircraft) || $matchFound;
            $results[] = (object) [
                'check' => 'aircraft',
                'expected' => $tour->aircraft ?? '*',
                'actual' => $flight->aircraft,
                'valid' => $aircraftValid,
            ];

            // Departed not empty
            $departedValid = ! empty($flight->departed);
            $results[] = (object) [
                'check' => 'departed_not_empty',
                'expected' => 'not empty',
                'actual' => $flight->departed,
                'valid' => $departedValid,
            ];

            if ($departedValid) {
                $departed = Carbon::parse($flight->departed);

                // Departed before end time
                $beforeEndValid = ! $departed->isAfter($current_end_time);
                $results[] = (object) [
                    'check' => 'departed_before_end',
                    'expected' => 'before '.$current_end_time,
                    'actual' => $departed,
                    'valid' => $beforeEndValid,
                ];

                // Departed after start time
                $afterStartValid = ! $departed->isBefore($current_start_time);
                $results[] = (object) [
                    'check' => 'departed_after_start',
                    'expected' => 'after '.$current_start_time,
                    'actual' => $departed,
                    'valid' => $afterStartValid,
                ];
            } else {
                // If departed is empty, these checks are automatically false
                $results[] = (object) [
                    'check' => 'departed_before_end',
                    'expected' => 'before '.$current_end_time,
                    'actual' => $flight->departed,
                    'valid' => false,
                ];
                $results[] = (object) [
                    'check' => 'departed_after_start',
                    'expected' => 'after '.$current_start_time,
                    'actual' => $flight->departed,
                    'valid' => false,
                ];
            }

            // Return a summary object for this flight
            return (object) [
                'flight_id' => $flight->id ?? null,
                'results' => $results,
                'all_valid' => collect($results)->every(fn ($r) => $r->valid),
                'time_to_enter' => $flight->departed,
            ];
        });
    }
}
