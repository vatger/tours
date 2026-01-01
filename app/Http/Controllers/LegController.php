<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourLeg;
use App\Models\TourLegUser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $validator =  $validator->after(function ($validator) use ($request) {
            $d = $request->input('date');
            $date = Carbon::create(
                $d['year'] ?? 0,
                $d['month']?? 0,
                $d['day']?? 0
            );
            if ($date->isToday() || $date->isFuture()) {
                $validator->errors()->add('date', 'The selected date must be before today.');
            }
        });
        $validated = $validator->validate();

        $data = $validated['date'];
        $date = Carbon::create($data['year'], $data['month'], $data['day']);

        // Format the “from” and “to” query parameters
        // For example, here we could do from midnight of that day until “end” of day (or some time)
        $from = $date->copy()->subHours(24)->startOfDay()->format('Y-m-d H:i');
        $to   = $date->copy()->endOfDay()->format('Y-m-d H:i');

        // Build the API URL
        $vatsimId = Auth::user()->id;
        $apiUrl = "https://api.statsim.net/api/Flights/VatsimId";
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
                    'status' => 'error',
                    'message' => 'Unable to connect to flight service.',
                ])->back();
        }
        if (!in_array($response->status(), [200, 404])) {
            return Inertia::flash('checkResult', [
                    'status' => 'error',
                    'message' => 'Flight service returned an unexpected error.',
                ])->back();
        }

        $leg = TourLeg::findOrFail($validated['leg']);
        $tour = Tour::findOrFail($leg->tour_id);
        $leg_user = TourLegUser::where('tour_leg_id', $leg->id)->where('user_id', Auth::user()->id)->firstOrFail();

        if ($leg_user->completed_at !== null) {
            return Inertia::flash('checkResult', [
                    'status' => 'error',
                    'message' => 'This leg is already completed.',
                ])->back();
        }

        $current_start_time = $tour->begins_at;
        $current_end_time = $tour->ends_at;
        if ($tour->require_order) {
            $all_legs = $tour->legs;
            foreach ($all_legs as $l) {
                $ul = TourLegUser::where('tour_leg_id', $l->id)->where('user_id', Auth::user()->id)->firstOrFail();
                if ($ul->completed_at !== null)
                    $current_start_time = $ul->completed_at;
            }
        }


        $flights = collect(!empty($response->json()) ? $response->json() : [])
            ->map(fn ($flight) => (object) $flight);

        $allFlights = $flights;
        $filteredFlights = $flights->filter(function ($flight) use ($tour, $leg, $current_end_time, $current_start_time) {
            if(strtoupper($flight->departure) != strtoupper($leg->departure_icao)) return false;
            if (strtoupper($flight->destination) != strtoupper($leg->arrival_icao)) return false;
            if(!empty($tour->aircraft) && !str_contains($tour->aircraft, $flight->aircraft)) return false;
            if(empty($flight->departed)) return false;
            $departed = Carbon::parse($flight->departed);
            if ($departed->isAfter($current_end_time)) return false;
            if ($departed->isBefore($current_start_time)) return false;
            return true;
        });

        if ($filteredFlights->isEmpty()) {
            return Inertia::flash('checkResult', [
                'status' => 'no_flights_found',
                'all_flights' => $allFlights->values(),
                'filtered_flights' => [],
            ])->back();
        }

        $firstFlight = (object) $filteredFlights->first();

        $leg_user->completed_at = Carbon::parse($firstFlight->departed);
        $leg_user->save();

        return Inertia::flash('checkResult', [
            'status' => 'found',
            'completed_at' => $leg_user->completed_at,
            'selected_flight' => $firstFlight,
            'all_flights' => $allFlights->values(),
            'filtered_flights' => $filteredFlights->values(),
        ])->back();
    }
}
