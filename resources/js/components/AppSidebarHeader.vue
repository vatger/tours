<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, LiveFlight } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { Plane, TriangleAlert } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted } from 'vue';

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
  }>(),
  {
    breadcrumbs: () => [],
  },
);

const page = usePage();
const liveFlight = computed(() => page.props.liveFlight as LiveFlight | null);
const quickStatsDown = computed(() => page.props.quickStatsDown as boolean);
let refreshTimer: number | undefined;

const formatTimestamp = (timestamp: string | null) =>
  timestamp
    ? new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
      }).format(new Date(timestamp))
    : null;

onMounted(() => {
  refreshTimer = window.setInterval(() => router.reload({ only: ['liveFlight', 'quickStatsDown'] }), 30_000);
});

onBeforeUnmount(() => {
  if (refreshTimer) {
    window.clearInterval(refreshTimer);
  }
});
</script>

<template>
  <header class="sticky top-0 z-20 shrink-0 border-b border-sidebar-border/70 bg-background shadow-sm">
    <div class="flex h-16 items-center gap-2 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
      <div class="flex items-center gap-2">
        <SidebarTrigger class="-ml-1" />
        <template v-if="breadcrumbs && breadcrumbs.length > 0">
          <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </template>
      </div>
    </div>
    <div v-if="quickStatsDown" class="flex items-center gap-2 border-t border-amber-500/30 bg-amber-500/10 px-6 py-2 text-sm text-amber-800 dark:text-amber-200 md:px-4">
      <TriangleAlert class="size-4 shrink-0" />
      Quick Stats is currently unavailable, so live-flight information cannot be shown.
    </div>
    <div v-if="liveFlight" class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-sidebar-border/50 bg-primary/5 px-6 py-2 text-sm md:px-4">
      <span class="flex items-center gap-2 font-medium text-primary">
        <span class="relative flex size-2" aria-label="Live flight active" role="status">
          <span class="absolute inline-flex size-full animate-ping rounded-full bg-green-500 opacity-75"></span>
          <span class="relative inline-flex size-2 rounded-full bg-green-500"></span>
        </span>
        <Plane class="size-4" />
        Live flight
      </span>
      <span class="font-semibold">{{ liveFlight.callsign }}</span>
      <span>{{ liveFlight.departure_airport }} → {{ liveFlight.arrival_airport }}</span>
      <span v-if="liveFlight.current_altitude != null">{{ liveFlight.current_altitude.toLocaleString() }} ft</span>
      <span v-if="liveFlight.current_groundspeed != null">{{ liveFlight.current_groundspeed }} kt</span>
      <span v-if="liveFlight.aircraft">{{ liveFlight.aircraft }}</span>
      <span>Departed: {{ formatTimestamp(liveFlight.departed_at) ?? 'Not departed' }}</span>
      <span>Arrived: {{ formatTimestamp(liveFlight.arrived_at) ?? 'En route' }}</span>
    </div>
  </header>
</template>
