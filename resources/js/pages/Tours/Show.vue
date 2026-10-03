<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, tours } from '@/routes';
import { type AirportCoordinate, type BreadcrumbItem, LiveFlight, Tour, TourFlight } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Circle, CircleCheck, CircleCheckBig, CirclePause, Map, Plane } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Components
import TourDetails from './components/TourDetails.vue';
import TourFlights from './components/TourFlights.vue';
import TourHeader from './components/TourHeader.vue';
import TourLegs from './components/TourLegs.vue';
import TourMap from './components/TourMap.vue';

const page = usePage();

// Get all routes (for sidebar)
const allTours = computed(() => page.props.tours_list as Array<Tour>);
// Current selected route
const currentTour = computed(() => page.props.current_tour as Tour);
const airportCoordinates = computed(() => (page.props.airport_coordinates ?? {}) as Record<string, AirportCoordinate>);
const liveFlight = computed(() => page.props.liveFlight as LiveFlight | null);
const myFlights = computed(() => (page.props.my_flights ?? []) as TourFlight[]);
const activeTab = ref<'overview' | 'flights'>('overview');

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Overview', href: dashboard().url },
  { title: currentTour?.value?.name, href: '#' },
];

const sidebarItems = computed(() =>
  allTours.value.map((tour: Tour) => ({
    title: tour.name,
    href: tours({ id: tour.id }).url,
    icon:
      tour.status != null
        ? tour.status.completed
          ? tour.status.badge_given
            ? CircleCheckBig
            : CircleCheck
          : CirclePause
        : Circle,
    isLive: Boolean(
      liveFlight.value &&
      tour.legs?.some(
        (leg) =>
          leg.departure_icao === liveFlight.value?.departure_airport &&
          leg.arrival_icao === liveFlight.value?.arrival_airport,
      ),
    ),
  })),
);
</script>

<template>
  <Head :title="currentTour?.name ?? ' '" />

  <AppLayout :breadcrumbs="breadcrumbs" :sidebarItems="sidebarItems">
    <div v-if="currentTour" class="flex flex-col gap-6 p-4">
      <TourHeader
        :tour="currentTour"
        :liveFlight="liveFlight"
        :signedUp="currentTour.status != null"
        :completed="currentTour.status != null && currentTour.status.completed"
        :badge_given="currentTour.status != null && currentTour.status.badge_given"
      />
      <div v-if="currentTour.status" class="inline-flex w-fit rounded-lg bg-muted p-1">
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
          :class="
            activeTab === 'overview'
              ? 'bg-background text-foreground shadow-xs'
              : 'text-muted-foreground hover:text-foreground'
          "
          @click="activeTab = 'overview'"
        >
          <Map class="size-4" /> Overview
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
          :class="
            activeTab === 'flights'
              ? 'bg-background text-foreground shadow-xs'
              : 'text-muted-foreground hover:text-foreground'
          "
          @click="activeTab = 'flights'"
        >
          <Plane class="size-4" /> My flights
          <span v-if="myFlights.length" class="text-xs text-muted-foreground">{{ myFlights.length }}</span>
        </button>
      </div>
      <template v-if="activeTab === 'overview'">
        <TourDetails
          :aircraft="currentTour.aircraft"
          :flightRules="currentTour.flight_rules"
          :requireOrder="currentTour.require_order"
        />
        <TourMap :legs="currentTour.legs" :airports="airportCoordinates" />
        <TourLegs :legs="currentTour.legs" :liveFlight="liveFlight" />
      </template>
      <TourFlights v-else :flights="myFlights" />
    </div>
  </AppLayout>
</template>
