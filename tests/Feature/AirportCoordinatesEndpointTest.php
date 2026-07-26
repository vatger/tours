<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AirportCoordinatesEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_coordinates_for_a_list_of_icaos(): void
    {
        Cache::clear();
        Http::fake([
            '*' => Http::response(implode("\n", [
                'ident,type,name,latitude_deg,longitude_deg',
                'EDDF,large_airport,Frankfurt Airport,50.033333,8.570556',
            ])),
        ]);

        $this->getJson(route('airports.index', ['icaos' => ['EDDF']]))
            ->assertOk()
            ->assertJsonPath('data.EDDF.icao', 'EDDF')
            ->assertJsonPath('data.EDDF.latitude', 50.033333)
            ->assertJsonPath('data.EDDF.longitude', 8.570556);
    }

    public function test_it_validates_icao_codes(): void
    {
        $this->getJson(route('airports.index', ['icaos' => ['not-an-icao']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('icaos.0');
    }
}
