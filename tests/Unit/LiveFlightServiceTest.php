<?php

namespace Tests\Unit;

use App\Services\LiveFlightService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveFlightServiceTest extends TestCase
{
    public function test_it_returns_the_live_flight_from_quick_stats(): void
    {
        config()->set('services.quick_stats.url', 'https://quick-stats.test/api');
        Cache::flush();
        Http::fake([
            'https://quick-stats.test/api/flights/1450775/live' => Http::response([
                'callsign' => 'DLH123',
                'departure_airport' => 'EDDF',
                'arrival_airport' => 'EDDM',
                'tracking_status' => 'up',
            ]),
        ]);

        $flight = app(LiveFlightService::class)->forPilot(1450775);

        $this->assertSame('DLH123', $flight['callsign']);
        Http::assertSent(fn ($request) => $request->url() === 'https://quick-stats.test/api/flights/1450775/live');
    }

    public function test_it_returns_null_when_quick_stats_has_no_live_flight(): void
    {
        config()->set('services.quick_stats.url', 'https://quick-stats.test/api');
        Cache::flush();
        Http::fake(['https://quick-stats.test/*' => Http::response(['message' => 'No live flight found.'], 404)]);

        $this->assertNull(app(LiveFlightService::class)->forPilot(1450775));
    }

    public function test_it_marks_quick_stats_down_when_tracking_is_unavailable(): void
    {
        config()->set('services.quick_stats.url', 'https://quick-stats.test/api');
        Cache::flush();
        Http::fake(['https://quick-stats.test/*' => Http::response([
            'message' => 'No live flight found.',
            'tracking_status' => 'down',
        ], 404)]);

        $this->assertTrue(app(LiveFlightService::class)->isDown(1450775));
    }

    public function test_it_reads_tracking_status_from_a_successful_live_flight_response(): void
    {
        config()->set('services.quick_stats.url', 'https://quick-stats.test/api');
        Cache::flush();
        Http::fake(['https://quick-stats.test/*' => Http::response([
            'callsign' => 'DLH123',
            'tracking_status' => 'down',
        ])]);

        $this->assertTrue(app(LiveFlightService::class)->isDown(1450775));
    }
}
