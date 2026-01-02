<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  aircraft: string | null;
  flightRules: string | null;
  requireOrder: boolean;
}>();

const aircraftNice = computed(() => (props.aircraft ? props.aircraft.replace(/,/g, ', ') : ''));
</script>

<template>
  <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
    <h2 class="mb-3 text-xl font-semibold">Further tour details</h2>
    <p v-if="aircraft" class="mb-2">
      <strong class="font-semibold">Allowed aircraft: </strong>
      {{ aircraftNice }}
    </p>
    <p v-else class="mb-2">
      <strong class="font-semibold">Allowed aircraft: </strong>
      all aircraft types are allowed
    </p>
    <p v-if="flightRules == 'v'" class="mb-2">
      <strong class="font-semibold">Allowed flight rules: </strong>
      VFR (Visual Flight Rules)
    </p>
    <p v-if="flightRules == 'i'" class="mb-2">
      <strong class="font-semibold">Allowed flight rules: </strong>
      IFR (Instrument Flight Rules)
    </p>
    <p v-if="flightRules == null" class="mb-2">
      <strong class="font-semibold">Allowed flight rules: </strong>
      any
    </p>
    <p v-if="requireOrder" class="mb-2">
      <strong class="font-semibold">Order: </strong>
      this tour must be flown in order
    </p>
    <p v-else class="mb-2">
      <strong class="font-semibold">Order: </strong>
      this tour can be flown in any oder
    </p>
  </div>
</template>
