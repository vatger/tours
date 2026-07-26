<?php

namespace Tests\Unit;

use App\Services\AirportCoordinateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AirportCoordinateServiceTest extends TestCase
{
    public function test_it_returns_requested_airport_coordinates_from_the_dataset(): void
    {
        Cache::clear();
        Http::fake([
            '*' => Http::response(implode("\n", [
                'ident,type,name,latitude_deg,longitude_deg',
                'EDDF,large_airport,Frankfurt Airport,50.033333,8.570556',
                'EDDM,large_airport,Munich Airport,48.353889,11.786111',
                'EGLL,large_airport,London Heathrow Airport,51.470600,-0.461941',
            ])),
        ]);

        $coordinates = app(AirportCoordinateService::class)->find(['eddm', 'EDDF', 'invalid']);

        $this->assertSame(['EDDF', 'EDDM'], array_keys($coordinates));
        $this->assertSame(50.033333, $coordinates['EDDF']['latitude']);
        $this->assertSame(11.786111, $coordinates['EDDM']['longitude']);
        Http::assertSentCount(1);
    }

    public function test_it_fails_gracefully_when_the_dataset_is_unavailable(): void
    {
        Cache::clear();
        Http::fake([
            '*' => Http::response('Unavailable', 503),
        ]);

        $this->assertSame([], app(AirportCoordinateService::class)->find(['EDDF']));
    }
}
