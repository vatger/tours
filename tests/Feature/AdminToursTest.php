<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourLeg;
use App\Models\TourLegUser;
use App\Models\TourUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminToursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['connect.admin_allowed_roles' => ['tour-admin']]);
    }

    public function test_users_without_an_allowed_sso_team_cannot_open_tour_admin(): void
    {
        $this->withSession(['sso_teams' => ['pilot']])
            ->actingAs(User::create(['firstname' => 'Pilot', 'lastname' => 'Tester']))
            ->get(route('admin.tours.index'))
            ->assertForbidden();
    }

    public function test_authenticated_user_can_open_tour_admin(): void
    {
        $this->withSession(['sso_teams' => ['tour-admin']])
            ->actingAs(User::create(['firstname' => 'Admin', 'lastname' => 'Tester']))
            ->get(route('admin.tours.index'))
            ->assertOk();
    }

    public function test_authenticated_user_can_create_a_tour_with_legs(): void
    {
        Storage::fake('public');

        $this->withSession(['sso_teams' => ['tour-admin']])
            ->actingAs(User::create(['firstname' => 'Admin', 'lastname' => 'Tester']))
            ->post(route('admin.tours.store'), [
                'name' => 'Alpine Test Tour',
                'description' => 'A test route.',
                'link' => 'https://example.test/tour',
                'img_url' => 'https://example.test/tour.png',
                'tour_image' => UploadedFile::fake()->image('tour.png', 1200, 675),
                'badge_image' => UploadedFile::fake()->image('badge.png', 300, 300),
                'aircraft' => 'A320',
                'flight_rules' => 'I',
                'require_order' => true,
                'begins_at' => '2026-09-01 10:00',
                'ends_at' => '2026-09-30 20:00',
                'legs' => [
                    ['departure_icao' => 'EDDF', 'arrival_icao' => 'LOWW'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tours', ['name' => 'Alpine Test Tour', 'require_order' => true]);
        $this->assertDatabaseHas('tour_legs', ['departure_icao' => 'EDDF', 'arrival_icao' => 'LOWW']);
        Storage::disk('public')->assertExists('tours/images/'.$this->latestStoredFile('tours/images'));
    }

    private function latestStoredFile(string $directory): string
    {
        return collect(Storage::disk('public')->allFiles($directory))->map(fn ($file) => str_replace($directory.'/', '', $file))->last();
    }

    public function test_active_tours_are_marked_for_the_editor(): void
    {
        $tour = Tour::create([
            'name' => 'Active Tour',
            'description' => 'Currently running.',
            'link' => 'https://example.test/tour',
            'img_url' => 'https://example.test/tour.png',
            'aircraft' => 'A320',
            'begins_at' => Carbon::now()->subHour(),
            'ends_at' => Carbon::now()->addHour(),
        ]);

        $this->withSession(['sso_teams' => ['tour-admin']])
            ->actingAs(User::create(['firstname' => 'Admin', 'lastname' => 'Tester']))
            ->get(route('admin.tours.edit', $tour))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tours/Edit')
                ->where('isActive', true));
    }

    public function test_admin_can_view_a_users_tour_progress(): void
    {
        $user = User::create(['firstname' => 'Pilot', 'lastname' => 'Tester']);
        $tour = Tour::create([
            'name' => 'Progress Tour',
            'description' => 'A progress test.',
            'link' => 'https://example.test/tour',
            'img_url' => '/storage/tours/images/test.png',
            'badge_img_url' => '/storage/tours/badges/test.png',
            'aircraft' => 'A320',
            'begins_at' => Carbon::now()->subDay(),
            'ends_at' => Carbon::now()->addDay(),
        ]);
        $leg = TourLeg::create(['tour_id' => $tour->id, 'departure_icao' => 'EDDF', 'arrival_icao' => 'LOWW']);
        TourUser::create(['tour_id' => $tour->id, 'user_id' => $user->id]);
        TourLegUser::create(['tour_leg_id' => $leg->id, 'user_id' => $user->id]);
        TourLegUser::where('tour_leg_id', $leg->id)->where('user_id', $user->id)->update(['completed_at' => Carbon::now(), 'statsim_flight_id' => 123]);

        $this->withSession(['sso_teams' => ['tour-admin']])
            ->actingAs(User::create(['firstname' => 'Admin', 'lastname' => 'Tester']))
            ->get(route('admin.users.tours', $user))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Tours')
                ->where('user.id', $user->id)
                ->where('tours.0.legs.0.completed_at', fn ($value) => $value !== null));

        Http::fake([
            'https://api.statsim.net/api/Flights/Id/123' => Http::response([
                'id' => 123, 'callsign' => 'VAT123', 'departure_airport' => 'EDDF', 'arrival_airport' => 'LOWW',
            ]),
        ]);

        $this->withSession(['sso_teams' => ['tour-admin']])
            ->actingAs(User::create(['firstname' => 'Admin', 'lastname' => 'Tester']))
            ->get(route('admin.users.flight', [$user, $tour, $leg]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Flight')
                ->where('flight.id', 123)
                ->where('flight.callsign', 'VAT123'));

    }
}
