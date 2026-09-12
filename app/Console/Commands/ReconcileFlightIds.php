<?php

namespace App\Console\Commands;

use App\Models\TourLegUser;
use App\Services\QuickStatsFlightService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ReconcileFlightIds extends Command
{
    protected $signature = 'tours:reconcile-flight-ids {--apply : Persist the proposed corrections}';

    protected $description = 'Reconcile legacy Statsim and Quick Stats flight IDs';

    public function handle(QuickStatsFlightService $quickStats): int
    {
        $apply = (bool) $this->option('apply');
        $changes = 0;
        $unresolved = 0;

        TourLegUser::query()
            ->where(function ($query) {
                $query->whereNotNull('fight_data_id')->orWhereNotNull('statsim_flight_id');
            })
            ->with('leg')
            ->chunkById(100, function ($statuses) use ($apply, $quickStats, &$changes, &$unresolved) {
                foreach ($statuses as $status) {
                    $result = $this->reconcile($status, $quickStats);

                    if ($result['unresolved']) {
                        $unresolved++;
                    }

                    if ($result['changed']) {
                        $changes++;
                        $this->line(sprintf(
                            '%s row %d: Quick Stats %s, Statsim %s%s',
                            $apply ? 'Would update' : 'Plan',
                            $status->id,
                            $result['quick_stats_id'] ?? '—',
                            $result['statsim_id'] ?? '—',
                            $result['note'] ? " ({$result['note']})" : '',
                        ));

                        if ($apply) {
                            $status->fight_data_id = $result['quick_stats_id'];
                            $status->statsim_flight_id = $result['statsim_id'];
                            $status->save();
                        }
                    }
                }
            });

        $this->info(sprintf(
            '%s %d proposed change(s); %d unresolved row(s).',
            $apply ? 'Applied' : 'Found',
            $changes,
            $unresolved,
        ));

        if (! $apply && $changes > 0) {
            $this->comment('Run again with --apply to persist these changes.');
        }

        return self::SUCCESS;
    }

    /** @return array{changed: bool, unresolved: bool, quick_stats_id: ?int, statsim_id: ?int, note: string} */
    private function reconcile(TourLegUser $status, QuickStatsFlightService $quickStats): array
    {
        $quickId = $status->fight_data_id;
        $statsimId = $status->statsim_flight_id;
        $quickResponse = $quickId ? $this->quickStatsFlight($quickId) : null;
        $statsimResponse = $statsimId ? $this->statsimFlight($statsimId) : null;
        $note = '';

        // Older rows sometimes stored the Statsim ID in fight_data_id. Move it when
        // Quick Stats does not know that number but Statsim does.
        if ($quickId && $quickResponse?->status() === 404) {
            $legacyStatsimResponse = $this->statsimFlight($quickId);
            if ($legacyStatsimResponse?->successful() === true) {
                $statsimResponse = $legacyStatsimResponse;
                $statsimId ??= $quickId;
                $quickId = null;
                $note = 'moved legacy Statsim ID';
            }
        }

        if ($statsimId && ! $statsimResponse) {
            $statsimResponse = $this->statsimFlight($statsimId);
        }

        if ($statsimId && $statsimResponse?->status() === 404) {
            $statsimId = null;
            $note = 'removed missing Statsim ID';
        }

        if ($statsimResponse?->successful() && ! $quickId) {
            $flight = $statsimResponse->json();
            $departed = $flight['departed'] ?? $flight['departed_at'] ?? null;
            $arrived = $flight['arrived'] ?? $flight['arrived_at'] ?? $departed;

            if ($departed) {
                $matched = $quickStats->findMatchingStatsimFlight(
                    (int) $status->user_id,
                    $flight,
                    Carbon::parse($departed)->subDay(),
                    Carbon::parse($arrived)->addDay(),
                );

                if ($matched) {
                    $quickId = $matched;
                    $note = 'matched by user, route, and approximate date';
                }
            }
        }

        $unresolved = false;
        if ($quickId && $quickResponse?->status() === 404 && ! $statsimResponse?->successful()) {
            // Preserve the Quick Stats ID as the fallback requested for records where
            // neither external system can currently verify the flight.
            $unresolved = true;
            $note = 'kept Quick Stats fallback';
        }

        return [
            'changed' => $quickId !== $status->fight_data_id || $statsimId !== $status->statsim_flight_id,
            'unresolved' => $unresolved,
            'quick_stats_id' => $quickId,
            'statsim_id' => $statsimId,
            'note' => $note,
        ];
    }

    private function quickStatsFlight(int $id): ?Response
    {
        try {
            return Http::acceptJson()->connectTimeout(2)->timeout(5)->get(
                rtrim(config('services.quick_stats.url'), '/')."/flights/id/{$id}",
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function statsimFlight(int $id): ?Response
    {
        try {
            return Http::withHeaders([
                'X-API-Key' => config('myconfig.statsim_api_key'),
            ])->acceptJson()->connectTimeout(2)->timeout(5)->get(
                "https://api.statsim.net/api/Flights/Id/{$id}",
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
