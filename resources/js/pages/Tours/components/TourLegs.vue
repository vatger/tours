<script setup lang="ts">
import CheckLeg from '@/pages/Tours/components/CheckLeg.vue';
import CheckLegResults from '@/pages/Tours/components/CheckLegResults.vue';
import type { Leg, LiveFlight } from '@/types';
import {
  BanIcon,
  CheckCircle2Icon,
  ClockIcon,
  MapIcon,
  PlaneLandingIcon,
  PlaneTakeoffIcon,
  RadioTowerIcon,
} from 'lucide-vue-next';

defineProps<{
  legs?: Array<Leg>;
  liveFlight?: LiveFlight | null;
}>();
</script>

<template>
  <CheckLegResults></CheckLegResults>
  <div class="rounded-xl border border-sidebar-border/70 bg-card/40 p-5 dark:border-sidebar-border">
    <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
      <MapIcon class="h-5 w-5 text-primary" />
      <span>Route Legs</span>
    </h2>
    <!-- Legs list -->
    <div v-if="legs && legs.length > 0" class="divide-y divide-muted/30">
      <div v-for="(leg, index) in legs" :key="leg.id" class="flex items-center justify-between py-3">
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2 text-sm text-muted-foreground">
            Leg {{ index + 1 }}
            <span
              v-if="
                liveFlight &&
                leg.departure_icao === liveFlight.departure_airport &&
                leg.arrival_icao === liveFlight.arrival_airport
              "
              class="relative flex size-2"
              aria-label="Live flight active"
              role="status"
            >
              <span class="absolute inline-flex size-full animate-ping rounded-full bg-green-500 opacity-75"></span>
              <span class="relative inline-flex size-2 rounded-full bg-green-500"></span>
            </span>
          </div>
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-1">
              <PlaneTakeoffIcon class="h-4 w-4 text-primary" />
              <span class="font-medium">{{ leg.departure_icao }}</span>
            </div>
            <span class="text-muted-foreground">→</span>
            <div class="flex items-center gap-1">
              <PlaneLandingIcon class="h-4 w-4 text-primary" />
              <span class="font-medium">{{ leg.arrival_icao }}</span>
            </div>
          </div>
          <div
            v-if="
              liveFlight &&
              leg.departure_icao === liveFlight.departure_airport &&
              leg.arrival_icao === liveFlight.arrival_airport
            "
            class="flex items-center gap-1 text-sm font-medium text-primary"
          >
            <RadioTowerIcon class="h-4 w-4" />
            Live: {{ liveFlight.callsign }} · {{ liveFlight.current_altitude?.toLocaleString() ?? '—' }} ft
          </div>
        </div>

        <!-- Status indicator -->
        <div class="flex items-center gap-2">
          <template v-if="leg.status && leg.status.completed_at">
            <CheckCircle2Icon class="h-4 w-4 text-green-500" />
            <span class="text-sm text-green-600 dark:text-green-400">Completed</span>
            <span class="text-sm text-muted">{{ leg.status.completed_at }}</span>
            <span v-if="leg.status.fight_data_id" class="text-sm text-muted"
              >Quick Stats #{{ leg.status.fight_data_id }}</span
            >
            <span v-if="leg.status.statsim_flight_id" class="text-sm text-muted"
              >Statsim #{{ leg.status.statsim_flight_id }}</span
            >
            <span v-if="!leg.status.fight_data_id && !leg.status.statsim_flight_id" class="text-sm text-muted"
              >Flight IDs unavailable</span
            >
          </template>
          <template v-else-if="leg.status && leg.status.completed_at">
            <CheckCircle2Icon class="h-4 w-4 text-green-500" />
            <span class="text-sm text-green-600 dark:text-green-400">Completed</span>
            <span class="text-sm text-muted">{{ leg.status.completed_at }}</span>
          </template>
          <template v-else-if="leg.status">
            <ClockIcon class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm text-muted-foreground">Pending</span>
            <CheckLeg :leg-id="leg.id"></CheckLeg>
          </template>
          <template v-else>
            <BanIcon class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm text-muted-foreground">Not registered</span>
          </template>
        </div>
      </div>
    </div>

    <div v-else class="text-sm text-muted-foreground">No route legs defined for this event.</div>
  </div>
</template>
