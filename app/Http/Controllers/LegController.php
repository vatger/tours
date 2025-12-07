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
        $from = $date->startOfDay()->format('Y-m-d H:i');
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
            $response = null;
        }
        $code = $response?->getStatusCode();
        if ($code != 200 && $code != 404) {
            abort(501, "Something went wrong: $code");
        }
        $flights = [];
        if ($response?->successful()) {
            $flights = $response->json();
        }
        $leg = TourLeg::findOrFail($validated['leg']);
        $tour = Tour::findOrFail($leg->tour_id);
        $leg_user = TourLegUser::where('tour_leg_id', $leg->id)->where('user_id', Auth::user()->id)->firstOrFail();
        if ($leg_user->completed_at !== null)
            abort(400, "Leg already complete");
        $flights = collect($flights);


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

        $flights = $flights->filter(function ($flight) use ($tour, $leg, $current_end_time, $current_start_time) {
            if(strtoupper($flight->departure) != strtoupper($leg->departure_icao)) return false;
            if(strtoupper($flight->arrival) != strtoupper($leg->arrival_icao)) return false;
            if(!empty($tour->aircraft) && !str_contains($tour->aircraft, $flight->aircraft)) return false;
            if(empty($flight->departed)) return false;
            $departed = Carbon::parse($flight->departed);
            if ($departed->isAfter($current_end_time)) return false;
            if ($departed->isBefore($current_start_time)) return false;
            return true;
        });

        if($flights->count() == 0)
            return response("No flights found");

        $first_flight = (object) $flights->first();
        $leg_user->completed_at = Carbon::parse($first_flight->departed);
        $leg_user->save();
        return response("Found");
    }
}
