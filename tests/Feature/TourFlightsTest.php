<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourLeg;
use App\Models\TourLegUser;
use App\Models\TourUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TourFlightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_pilot_can_view_their_completed_flight_details(): void
    {
        $pilot = User::create(['firstname' => 'Test', 'lastname' => 'Pilot']);
        $tour = Tour::create([
            'name' => 'Flight history',
            'description' => 'A tour.',
            'link' => 'https://example.test/tour',
            'img_url' => 'https://example.test/tour.png',
            'aircraft' => 'A320',
            'begins_at' => Carbon::now()->subDay(),
            'ends_at' => Carbon::now()->addDay(),
        ]);
        $leg = TourLeg::create(['tour_id' => $tour->id, 'departure_icao' => 'EDDF', 'arrival_icao' => 'LOWW']);
        TourUser::create(['tour_id' => $tour->id, 'user_id' => $pilot->id]);
        $progress = TourLegUser::create([
            'tour_leg_id' => $leg->id,
            'user_id' => $pilot->id,
            'fight_data_id' => 42,
        ]);
        $progress->completed_at = Carbon::parse('2026-10-01 12:00:00 UTC');
        $progress->save();

        Http::fake([
            '*/flights/id/42' => Http::response([
                'callsign' => 'DLH123',
                'aircraft' => 'A320',
                'route' => 'TOBAK Z74 SULUS',
                'departed_at' => '2026-10-01T10:00:00Z',
                'arrived_at' => '2026-10-01T11:15:00Z',
            ]),
            '*' => Http::response('ident,type,name,latitude_deg,longitude_deg'),
        ]);

        $response = $this->actingAs($pilot)
            ->get(route('tours', $tour))
            ->assertOk();

        $response
            ->assertInertia(fn ($page) => $page
                ->component('Tours/Show')
                ->where('my_flights.0.departure_icao', 'EDDF')
                ->where('my_flights.0.arrival_icao', 'LOWW')
                ->where('my_flights.0.callsign', 'DLH123')
                ->where('my_flights.0.flight_plan', 'TOBAK Z74 SULUS')
                ->where('my_flights.0.departed_at', '2026-10-01T10:00:00Z')
                ->where('my_flights.0.arrived_at', '2026-10-01T11:15:00Z'));
    }
}
