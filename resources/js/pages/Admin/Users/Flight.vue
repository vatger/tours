<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Plane } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  userId: number;
  flightId: number;
  quickStatsFlightId: number | null;
  statsimFlightId: number | null;
  tour: { id: number; name: string };
  leg: { id: number; departure_icao: string; arrival_icao: string };
  flight: Record<string, unknown> | null;
  flightStatus: 'found' | 'not_tracked' | 'unavailable';
  flightSource: 'quick_stats' | 'statsim' | null;
}>();
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users & progress', href: '/admin/users' },
  { title: `User #${props.userId}`, href: `/admin/users/${props.userId}/tours` },
  { title: `Flight #${props.flightId}`, href: '#' },
];
const label = (key: string) => key.replaceAll('_', ' ').replace(/\b\w/g, (character) => character.toUpperCase());
const entries = computed(() => Object.entries(props.flight ?? {}));
</script>

<template>
  <Head :title="`Flight #${props.flightId}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 p-6 lg:p-10">
      <div class="flex items-start gap-4">
        <Button variant="outline" size="icon" as-child
          ><Link :href="`/admin/users/${props.userId}/tours`" aria-label="Back to user tours"
            ><ArrowLeft class="size-4" /></Link
        ></Button>
        <div>
          <p class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Flight details</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-tight">Flight #{{ props.flightId }}</h1>
          <p class="mt-2 text-muted-foreground">
            {{ props.tour.name }} · {{ props.leg.departure_icao }} → {{ props.leg.arrival_icao }}
          </p>
        </div>
      </div>

      <section v-if="props.flight" class="rounded-xl border bg-card p-6 shadow-xs">
        <div class="mb-5 flex items-center gap-3 border-b pb-5">
          <span class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary"
            ><Plane class="size-5"
          /></span>
          <div>
            <h2 class="font-semibold">Stored flight record</h2>
            <p class="text-sm text-muted-foreground">
              Source: {{ props.flightSource === 'quick_stats' ? 'Quick Stats' : 'Statsim' }}. All available fields are
              shown below.
            </p>
          </div>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="[key, value] in entries" :key="key" class="rounded-lg bg-muted/30 p-3">
            <dt class="text-xs font-medium text-muted-foreground uppercase">{{ label(key) }}</dt>
            <dd class="mt-1 text-sm font-medium break-words">{{ value === null || value === '' ? '—' : value }}</dd>
          </div>
        </dl>
      </section>
      <section v-else class="rounded-xl border border-dashed p-12 text-center">
        <h2 class="text-lg font-semibold">Flight details unavailable</h2>
        <p v-if="props.flightStatus === 'not_tracked'" class="mt-2 text-muted-foreground">
          Statsim has no record for flight #{{ props.statsimFlightId ?? props.flightId }}. It may not have been tracked
          or may no longer be retained.
        </p>
        <p v-else class="mt-2 text-muted-foreground">
          Flight details are currently unavailable for Quick Stats #{{ props.quickStatsFlightId ?? '—' }} / Statsim #{{
            props.statsimFlightId ?? '—'
          }}.
        </p>
      </section>
    </main>
  </AppLayout>
</template>
