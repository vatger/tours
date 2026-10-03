<script setup lang="ts">
import type { TourFlight } from '@/types';
import { CalendarClock, FileText, Plane, PlaneLanding, PlaneTakeoff } from 'lucide-vue-next';

defineProps<{
  flights: TourFlight[];
}>();

const formatTime = (value: string | null) => {
  if (!value) return 'Not available';

  const date = new Date(value);
  return Number.isNaN(date.getTime())
    ? value
    : new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(date);
};
</script>

<template>
  <section class="overflow-hidden rounded-xl border border-border bg-card">
    <header class="border-b border-border px-5 py-4">
      <div class="flex items-center gap-2">
        <Plane class="size-5 text-ring" />
        <h2 class="text-xl font-semibold">My flights</h2>
      </div>
      <p class="mt-1 text-sm text-muted-foreground">
        Your completed tour legs, including the recorded flight plan and times when available.
      </p>
    </header>

    <div v-if="flights.length" class="divide-y divide-border">
      <article v-for="flight in flights" :key="flight.leg_id" class="p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-sm font-medium text-muted-foreground">Leg {{ flight.leg_number }}</p>
            <div class="mt-1 flex items-center gap-2 text-lg font-semibold">
              <span class="inline-flex items-center gap-1"
                ><PlaneTakeoff class="size-4 text-primary" />{{ flight.departure_icao }}</span
              >
              <span class="text-muted-foreground">→</span>
              <span class="inline-flex items-center gap-1"
                ><PlaneLanding class="size-4 text-primary" />{{ flight.arrival_icao }}</span
              >
            </div>
          </div>
          <div class="text-sm sm:text-right">
            <p v-if="flight.callsign" class="font-medium">
              {{ flight.callsign }}<span v-if="flight.aircraft"> · {{ flight.aircraft }}</span>
            </p>
            <p class="text-muted-foreground">Completed {{ formatTime(flight.completed_at) }}</p>
          </div>
        </div>

        <dl class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div class="rounded-lg bg-muted/40 p-3">
            <dt class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground uppercase">
              <PlaneTakeoff class="size-3.5" /> Departure
            </dt>
            <dd class="mt-1 text-sm font-medium">{{ formatTime(flight.departed_at) }}</dd>
          </div>
          <div class="rounded-lg bg-muted/40 p-3">
            <dt class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground uppercase">
              <PlaneLanding class="size-3.5" /> Arrival
            </dt>
            <dd class="mt-1 text-sm font-medium">{{ formatTime(flight.arrived_at) }}</dd>
          </div>
          <div class="rounded-lg bg-muted/40 p-3">
            <dt class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground uppercase">
              <CalendarClock class="size-3.5" /> Flight record
            </dt>
            <dd class="mt-1 text-sm font-medium">
              <span v-if="flight.source === 'quick_stats'">Quick Stats #{{ flight.quick_stats_flight_id }}</span>
              <span v-else-if="flight.source === 'statsim'">Statsim #{{ flight.statsim_flight_id }}</span>
              <span v-else>Historical record unavailable</span>
            </dd>
          </div>
        </dl>

        <div class="mt-3 rounded-lg border border-border p-3">
          <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground uppercase">
            <FileText class="size-3.5" /> Flight plan
          </p>
          <p class="mt-1 font-mono text-sm break-words">
            {{ flight.flight_plan || 'No flight plan was retained for this flight.' }}
          </p>
        </div>
      </article>
    </div>

    <div v-else class="flex min-h-56 flex-col items-center justify-center px-6 text-center">
      <Plane class="size-8 text-muted-foreground" />
      <h3 class="mt-3 font-semibold">No completed flights yet</h3>
      <p class="mt-1 max-w-md text-sm text-muted-foreground">
        Complete a tour leg and its flight record will appear here.
      </p>
    </div>
  </section>
</template>
