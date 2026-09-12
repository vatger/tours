<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminTourController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Tours/Index', [
            'tours' => Tour::withCount('legs')->with('legs')->latest('begins_at')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tours/Edit', [
            'tour' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tour = $this->saveTour($request, new Tour);

        return to_route('admin.tours.edit', $tour)->with('success', 'Tour created.');
    }

    public function edit(Tour $tour): Response
    {
        return Inertia::render('Admin/Tours/Edit', [
            'tour' => $tour->load(['legs' => fn ($query) => $query->withCount('completedUsers')]),
            'isActive' => $tour->begins_at?->lte(Carbon::now()) && $tour->ends_at?->gte(Carbon::now()),
        ]);
    }

    public function update(Request $request, Tour $tour): RedirectResponse
    {
        $this->saveTour($request, $tour);

        return to_route('admin.tours.edit', $tour)->with('success', 'Tour updated.');
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        $tour->delete();

        return to_route('admin.tours.index')->with('success', 'Tour deleted.');
    }

    private function saveTour(Request $request, Tour $tour): Tour
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:32'],
            'description' => ['required', 'string'],
            'link' => ['required', 'string', 'max:2048'],
            'aircraft' => ['required', 'string', 'max:255'],
            'tour_image' => [$tour->img_url ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:width=1200,height=675'],
            'badge_image' => [$tour->badge_img_url ? 'nullable' : 'required', 'file', 'mimes:png', 'max:2048', 'dimensions:width=300,height=300'],
            'flight_rules' => ['nullable', 'string', 'size:1', 'in:i,v,I,V'],
            'require_order' => ['boolean'],
            'begins_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:begins_at'],
            'forum_badge_id' => ['nullable', 'integer'],
            'legs' => ['array'],
            'legs.*.id' => ['nullable', 'integer'],
            'legs.*.departure_icao' => ['required', 'string', 'size:4', 'alpha_num'],
            'legs.*.arrival_icao' => ['required', 'string', 'size:4', 'alpha_num'],
        ]);

        if (! empty($validated['flight_rules'])) {
            $validated['flight_rules'] = strtolower($validated['flight_rules']);
        }

        return DB::transaction(function () use ($tour, $validated, $request) {
            $tourImageUrl = $request->hasFile('tour_image')
                ? Storage::disk('public')->url($request->file('tour_image')->storePublicly('tours/images', 'public'))
                : null;
            $badgeImageUrl = $request->hasFile('badge_image')
                ? Storage::disk('public')->url($request->file('badge_image')->storePublicly('tours/badges', 'public'))
                : null;

            $tour->fill(collect($validated)->except('legs', 'tour_image', 'badge_image')->all());
            $tour->img_url = $tourImageUrl ?? $tour->img_url;
            $tour->badge_img_url = $badgeImageUrl ?? $tour->badge_img_url;
            $tour->save();

            $keptLegIds = [];
            foreach ($validated['legs'] ?? [] as $leg) {
                $model = ! empty($leg['id'])
                    ? $tour->legs()->whereKey($leg['id'])->firstOrFail()
                    : $tour->legs()->make();
                $model->fill([
                    'departure_icao' => strtoupper($leg['departure_icao']),
                    'arrival_icao' => strtoupper($leg['arrival_icao']),
                ]);
                $model->save();
                $keptLegIds[] = $model->id;
            }

            $tour->legs()->whereNotIn('id', $keptLegIds ?: [0])->delete();

            return $tour;
        });
    }
}
