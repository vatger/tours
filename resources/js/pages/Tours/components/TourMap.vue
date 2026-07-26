<script setup lang="ts">
import landData from '@/data/natural-earth-land.json';
import type { AirportCoordinate, Leg } from '@/types';
import { useElementSize } from '@vueuse/core';
import { geoMercator, geoPath } from 'd3-geo';
import type { Feature, FeatureCollection, MultiPoint, MultiPolygon, Polygon } from 'geojson';
import { MapIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
  legs?: Array<Leg>;
  airports: Record<string, AirportCoordinate>;
}>();

const mapContainer = ref<HTMLElement | null>(null);
const { width: containerWidth } = useElementSize(mapContainer);
const viewport = computed(() => {
  const width = Math.max(Math.round(containerWidth.value || 1600), 320);
  const compact = width < 640;

  return {
    width,
    height: compact ? 320 : 360,
    padding: compact ? 30 : 52,
  };
});

type RoutePoint = AirportCoordinate & {
  key: string;
  x: number;
  y: number;
};

type MarkerState = 'completed' | 'current' | 'planned';

type MapMarker = RoutePoint & {
  state: MarkerState;
};

type RouteSegment = {
  leg: Leg;
  index: number;
  from: RoutePoint;
  to: RoutePoint;
  completed: boolean;
  current: boolean;
};

type MapLabel = {
  marker: MapMarker;
  x: number;
  y: number;
  anchor: 'start' | 'middle' | 'end';
};

const completedLegs = computed(() => props.legs?.filter((leg) => leg.status?.completed_at).length ?? 0);
const totalLegs = computed(() => props.legs?.length ?? 0);
const plannedLegs = computed(() => Math.max(totalLegs.value - completedLegs.value, 0));
const progress = computed(() =>
  totalLegs.value === 0 ? 0 : Math.round((completedLegs.value / totalLegs.value) * 100),
);

const geometry = computed(() => {
  const { width, height, padding } = viewport.value;
  const sourceSegments = (props.legs ?? [])
    .map((leg, index) => {
      const departure = props.airports[leg.departure_icao];
      const arrival = props.airports[leg.arrival_icao];

      return departure && arrival ? { leg, index, departure, arrival } : null;
    })
    .filter((segment): segment is NonNullable<typeof segment> => segment !== null);

  if (sourceSegments.length === 0) {
    return {
      landPath: '',
      labels: [] as MapLabel[],
      markers: [] as MapMarker[],
      segments: [] as RouteSegment[],
    };
  }

  const routePoints: Array<AirportCoordinate & { key: string }> = [];

  sourceSegments.forEach(({ leg, departure, arrival }) => {
    [departure, arrival].forEach((airport, pointIndex) => {
      routePoints.push({
        ...airport,
        key: `${leg.id}-${pointIndex === 0 ? 'departure' : 'arrival'}`,
      });
    });
  });

  let previousLongitude: number | null = null;
  const unwrappedLongitudes = routePoints.map((point) => {
    let longitude = point.longitude;

    if (previousLongitude !== null) {
      while (longitude - previousLongitude > 180) longitude -= 360;
      while (longitude - previousLongitude < -180) longitude += 360;
    }

    previousLongitude = longitude;

    return longitude;
  });
  const routeCenterLongitude = (Math.min(...unwrappedLongitudes) + Math.max(...unwrappedLongitudes)) / 2;
  const routeLatitudes = routePoints.map((point) => point.latitude);
  const routeCenterLatitude = (Math.min(...routeLatitudes) + Math.max(...routeLatitudes)) / 2;
  const longitudeSpan = Math.max(...unwrappedLongitudes) - Math.min(...unwrappedLongitudes);
  const latitudeSpan = Math.max(...routeLatitudes) - Math.min(...routeLatitudes);
  const fitLongitudeSpan = Math.max(longitudeSpan, 6);
  const fitLatitudeSpan = Math.max(latitudeSpan, 4);
  const normalizeLongitude = (longitude: number) => ((longitude + 540) % 360) - 180;
  const clampLatitude = (latitude: number) => Math.max(-82, Math.min(82, latitude));
  const routeFeature: Feature<MultiPoint> = {
    type: 'Feature',
    properties: {},
    geometry: {
      type: 'MultiPoint',
      coordinates: [
        ...routePoints.map((point) => [point.longitude, point.latitude]),
        [
          normalizeLongitude(routeCenterLongitude - fitLongitudeSpan / 2),
          clampLatitude(routeCenterLatitude - fitLatitudeSpan / 2),
        ],
        [
          normalizeLongitude(routeCenterLongitude + fitLongitudeSpan / 2),
          clampLatitude(routeCenterLatitude + fitLatitudeSpan / 2),
        ],
      ],
    },
  };
  const projection = geoMercator()
    .rotate([-routeCenterLongitude, 0])
    .fitExtent(
      [
        [padding, padding],
        [width - padding, height - padding],
      ],
      routeFeature,
    );

  projection.clipExtent([
    [0, 0],
    [width, height],
  ]);

  const projectedPoints = routePoints.map<RoutePoint>((point) => {
    const [x, y] = projection([point.longitude, point.latitude]) ?? [width / 2, height / 2];

    return { ...point, x, y };
  });
  const landPath = geoPath(projection)(landData as unknown as FeatureCollection<Polygon | MultiPolygon>) ?? '';

  const currentLegIndex = (props.legs ?? []).findIndex((leg) => leg.status && !leg.status.completed_at);
  const segments = sourceSegments.map<RouteSegment>(({ leg, index }, sourceIndex) => ({
    leg,
    index,
    from: projectedPoints[sourceIndex * 2],
    to: projectedPoints[sourceIndex * 2 + 1],
    completed: Boolean(leg.status?.completed_at),
    current: index === currentLegIndex,
  }));

  const markerMap = new Map<string, MapMarker>();
  const statePriority: Record<MarkerState, number> = { completed: 1, planned: 2, current: 3 };

  segments.forEach((segment) => {
    const state: MarkerState = segment.current ? 'current' : segment.completed ? 'completed' : 'planned';

    [segment.from, segment.to].forEach((point) => {
      const existing = markerMap.get(point.icao);

      if (!existing || statePriority[state] > statePriority[existing.state]) {
        markerMap.set(point.icao, { ...point, state });
      }
    });
  });

  const markers = Array.from(markerMap.values());
  const importantIcaos = new Set<string>([
    segments[0].from.icao,
    segments[segments.length - 1].to.icao,
    ...segments.filter((segment) => segment.current).flatMap((segment) => [segment.from.icao, segment.to.icao]),
  ]);
  const labelCandidates = [...markers].sort(
    (left, right) => Number(importantIcaos.has(right.icao)) - Number(importantIcaos.has(left.icao)),
  );
  const occupied: Array<{ x: number; y: number; width: number; height: number }> = [];
  const labels: MapLabel[] = [];
  const labelLimit = markers.length > 20 ? 10 : markers.length > 12 ? 12 : markers.length;

  const overlaps = (candidate: { x: number; y: number; width: number; height: number }, marker: MapMarker) => {
    if (
      candidate.x < 8 ||
      candidate.y < 8 ||
      candidate.x + candidate.width > width - 8 ||
      candidate.y + candidate.height > height - 8
    ) {
      return true;
    }

    const overlapsLabel = occupied.some(
      (rect) =>
        candidate.x < rect.x + rect.width + 6 &&
        candidate.x + candidate.width + 6 > rect.x &&
        candidate.y < rect.y + rect.height + 4 &&
        candidate.y + candidate.height + 4 > rect.y,
    );

    if (overlapsLabel) return true;

    return markers.some(
      (other) =>
        other.icao !== marker.icao &&
        other.x > candidate.x - 7 &&
        other.x < candidate.x + candidate.width + 7 &&
        other.y > candidate.y - 7 &&
        other.y < candidate.y + candidate.height + 7,
    );
  };

  labelCandidates.forEach((marker) => {
    if (labels.length >= labelLimit && !importantIcaos.has(marker.icao)) return;

    const labelWidth = 48;
    const labelHeight = 18;
    const positions = [
      {
        rect: { x: marker.x - labelWidth / 2, y: marker.y - 30, width: labelWidth, height: labelHeight },
        label: { x: marker.x, y: marker.y - 16, anchor: 'middle' as const },
      },
      {
        rect: { x: marker.x - labelWidth / 2, y: marker.y + 13, width: labelWidth, height: labelHeight },
        label: { x: marker.x, y: marker.y + 27, anchor: 'middle' as const },
      },
      {
        rect: { x: marker.x + 13, y: marker.y - 10, width: labelWidth, height: labelHeight },
        label: { x: marker.x + 16, y: marker.y + 4, anchor: 'start' as const },
      },
      {
        rect: { x: marker.x - labelWidth - 13, y: marker.y - 10, width: labelWidth, height: labelHeight },
        label: { x: marker.x - 16, y: marker.y + 4, anchor: 'end' as const },
      },
    ];
    const selectedPosition = positions.find((position) => !overlaps(position.rect, marker));

    if (!selectedPosition) return;

    occupied.push(selectedPosition.rect);
    labels.push({ marker, ...selectedPosition.label });
  });

  return { landPath, labels, markers, segments };
});

const missingAirportCount = computed(() => {
  const requiredAirports = new Set((props.legs ?? []).flatMap((leg) => [leg.departure_icao, leg.arrival_icao]));

  return Array.from(requiredAirports).filter((icao) => !props.airports[icao]).length;
});
</script>

<template>
  <section class="overflow-hidden rounded-xl border border-border bg-card">
    <header class="flex flex-col gap-4 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="flex items-center gap-2">
          <MapIcon class="size-5 text-ring" />
          <h2 class="text-xl font-semibold">Tour progress</h2>
        </div>
        <p class="mt-1 text-sm text-muted-foreground">
          {{ completedLegs }} of {{ totalLegs }} legs completed / {{ plannedLegs }} remaining
        </p>
      </div>

      <div class="flex min-w-48 items-center gap-3">
        <div class="h-2 flex-1 overflow-hidden rounded-full bg-muted">
          <div class="h-full rounded-full bg-ring transition-[width]" :style="{ width: `${progress}%` }"></div>
        </div>
        <span class="w-10 text-right text-sm font-medium">{{ progress }}%</span>
      </div>
    </header>

    <div v-if="geometry.segments.length > 0" ref="mapContainer" class="bg-secondary/25">
      <svg
        :viewBox="`0 0 ${viewport.width} ${viewport.height}`"
        class="block w-full"
        :style="{ height: `${viewport.height}px` }"
        role="img"
        :aria-label="`Tour route map showing ${completedLegs} completed and ${plannedLegs} planned legs`"
      >
        <path :d="geometry.landPath" class="fill-muted/70" fill-rule="evenodd" />

        <g v-for="segment in geometry.segments" :key="segment.leg.id">
          <title>
            Leg {{ segment.index + 1 }}: {{ segment.leg.departure_icao }} to {{ segment.leg.arrival_icao }} -
            {{ segment.completed ? 'Completed' : segment.current ? 'Current' : 'Planned' }}
          </title>
          <line
            :x1="segment.from.x"
            :y1="segment.from.y"
            :x2="segment.to.x"
            :y2="segment.to.y"
            class="stroke-background/85"
            stroke-width="6"
            stroke-linecap="round"
          />
          <line
            :x1="segment.from.x"
            :y1="segment.from.y"
            :x2="segment.to.x"
            :y2="segment.to.y"
            :class="[
              segment.completed ? 'stroke-ring' : segment.current ? 'stroke-primary' : 'stroke-muted-foreground/60',
            ]"
            :stroke-dasharray="segment.completed || segment.current ? undefined : '6 8'"
            :stroke-width="segment.current ? 4 : 3"
            stroke-linecap="round"
          />
        </g>

        <g v-for="marker in geometry.markers" :key="marker.icao" :transform="`translate(${marker.x} ${marker.y})`">
          <title>{{ marker.icao }} - {{ marker.name }}</title>
          <circle r="8" class="fill-card/95 stroke-background" stroke-width="3" />
          <circle
            r="5"
            :class="[
              marker.state === 'completed'
                ? 'fill-ring'
                : marker.state === 'current'
                  ? 'fill-primary'
                  : 'fill-muted-foreground',
            ]"
          />
        </g>

        <text
          v-for="label in geometry.labels"
          :key="`label-${label.marker.icao}`"
          :x="label.x"
          :y="label.y"
          :text-anchor="label.anchor"
          class="fill-foreground text-[11px] font-semibold"
          paint-order="stroke"
          stroke="var(--card)"
          stroke-width="4"
          stroke-linejoin="round"
        >
          {{ label.marker.icao }}
        </text>
      </svg>
    </div>

    <div v-else class="flex min-h-56 flex-col items-center justify-center px-6 text-center">
      <MapIcon class="size-8 text-muted-foreground" />
      <h3 class="mt-3 font-semibold">Route map unavailable</h3>
      <p class="mt-1 max-w-md text-sm text-muted-foreground">
        Airport coordinates could not be loaded. The leg list below is still available.
      </p>
    </div>

    <footer
      class="flex flex-col gap-3 border-t border-border px-5 py-3 text-xs text-muted-foreground lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
        <span class="flex items-center gap-1.5">
          <span class="h-0.5 w-5 bg-ring"></span>
          Completed
        </span>
        <span class="flex items-center gap-1.5">
          <span class="h-0.5 w-5 bg-primary"></span>
          Current
        </span>
        <span class="flex items-center gap-1.5">
          <span class="w-5 border-t-2 border-dashed border-muted-foreground"></span>
          Planned
        </span>
        <span v-if="missingAirportCount > 0">
          {{ missingAirportCount }} {{ missingAirportCount === 1 ? 'airport is' : 'airports are' }} missing coordinates.
        </span>
      </div>
      <span class="flex shrink-0 items-center gap-2">
        <a
          href="https://www.naturalearthdata.com/"
          target="_blank"
          rel="noreferrer"
          class="transition-colors hover:text-foreground"
        >
          Map: Natural Earth
        </a>
        <span aria-hidden="true">/</span>
        <a
          href="https://ourairports.com/data/"
          target="_blank"
          rel="noreferrer"
          class="transition-colors hover:text-foreground"
        >
          Airports: OurAirports
        </a>
      </span>
    </footer>
  </section>
</template>
