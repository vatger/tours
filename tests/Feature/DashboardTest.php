<?php

namespace Tests\Feature;

use App\Jobs\CheckTourCompletedUser;
use App\Jobs\CheckTourUser;
use App\Models\Tour;
use App\Models\TourLeg;
use App\Models\TourLegUser;
use App\Models\TourUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::create(['firstname' => 'Test', 'lastname' => 'Pilot']);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_enrolled_pilot_can_start_a_rescan_for_their_tour(): void
    {
        Bus::fake();

        $pilot = User::create(['firstname' => 'Test', 'lastname' => 'Pilot']);
        $tour = Tour::create([
            'name' => 'Rescan Tour',
            'description' => 'A tour.',
            'link' => 'https://example.test/tour',
            'img_url' => 'https://example.test/tour.png',
            'aircraft' => 'A320',
            'begins_at' => Carbon::now()->subDay(),
            'ends_at' => Carbon::now()->addDay(),
        ]);
        TourUser::create(['tour_id' => $tour->id, 'user_id' => $pilot->id]);

        $this->actingAs($pilot)
            ->post(route('tours.rescan', $tour))
            ->assertRedirect(route('tours', $tour))
            ->assertSessionHas('success', 'Tour rescan started. Your completed legs will update shortly.');

        Bus::assertChained([
            fn (CheckTourUser $job) => $job->user->is($pilot) && $job->tour->is($tour),
            fn (CheckTourCompletedUser $job) => $job->user->is($pilot) && $job->tour->is($tour),
        ]);
    }

    public function test_pilot_cannot_rescan_a_tour_they_have_not_joined(): void
    {
        Bus::fake();

        $pilot = User::create(['firstname' => 'Test', 'lastname' => 'Pilot']);
        $tour = Tour::create([
            'name' => 'Unjoined Tour',
            'description' => 'A tour.',
            'link' => 'https://example.test/tour',
            'img_url' => 'https://example.test/tour.png',
            'aircraft' => 'A320',
            'begins_at' => Carbon::now()->subDay(),
            'ends_at' => Carbon::now()->addDay(),
        ]);

        $this->actingAs($pilot)->post(route('tours.rescan', $tour))->assertNotFound();
        Bus::assertNothingDispatched();
    }

    public function test_dashboard_shows_community_and_personal_tour_activity(): void
    {
        $pilot = User::create(['firstname' => 'Test', 'lastname' => 'Pilot']);
        $otherPilot = User::create(['firstname' => 'Other', 'lastname' => 'Pilot']);
        $tour = Tour::create([
            'name' => 'Dashboard Tour',
            'description' => 'A tour for the dashboard test.',
            'link' => 'https://example.test/tour',
            'img_url' => 'https://example.test/tour.png',
            'aircraft' => 'A320',
            'begins_at' => Carbon::now()->subDay(),
            'ends_at' => Carbon::now()->addDay(),
        ]);
        $completedLeg = TourLeg::create(['tour_id' => $tour->id, 'departure_icao' => 'EDDF', 'arrival_icao' => 'LOWW']);
        $nextLeg = TourLeg::create(['tour_id' => $tour->id, 'departure_icao' => 'LOWW', 'arrival_icao' => 'EDDM']);

        $pilotRegistration = TourUser::create(['tour_id' => $tour->id, 'user_id' => $pilot->id]);
        $pilotRegistration->badge_given = true;
        $pilotRegistration->save();
        TourUser::create(['tour_id' => $tour->id, 'user_id' => $otherPilot->id]);
        $completedStatus = TourLegUser::create(['tour_leg_id' => $completedLeg->id, 'user_id' => $pilot->id]);
        $completedStatus->completed_at = Carbon::now()->subDay();
        $completedStatus->save();
        TourLegUser::create(['tour_leg_id' => $nextLeg->id, 'user_id' => $pilot->id]);

        Http::fake([
            "*/flights/{$pilot->id}/sessions*" => Http::response([
                [
                    'id' => 500,
                    'callsign' => 'DLH123',
                    'departure_airport' => 'EDDF',
                    'arrival_airport' => 'LOWW',
                    'aircraft' => 'A320',
                    'flight_type' => 'I',
                    'departed_at' => '2026-10-01T10:00:00Z',
                    'arrived_at' => '2026-10-01T11:15:00Z',
                ],
            ]),
        ]);

        $this->actingAs($pilot)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('communityStats.legsFlownLast30Days', 1)
                ->where('communityStats.tourRegistrations', 2)
                ->where('communityStats.badgesAwarded', 1)
                ->where('communityStats.participatingPilots', 2)
                ->where('personalStats.legsFlown', 1)
                ->where('personalStats.activeTours', 1)
                ->where('personalStats.badgesEarned', 1)
                ->where('recentLegs.0.departureIcao', 'EDDF')
                ->where('nextLegs.0.departureIcao', 'LOWW')
                ->where('recentNetworkFlights.0.callsign', 'DLH123')
                ->where('recentNetworkFlights.0.flightType', 'IFR'));
    }
}
