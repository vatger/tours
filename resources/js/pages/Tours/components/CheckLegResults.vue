<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { CheckResult } from '@/types/LegMissing';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const openResult = ref(false);
const checkResult = ref<CheckResult | undefined>();

router.on('flash', (event) => {
  if (event.detail.flash.checkResult) {
    checkResult.value = event.detail.flash.checkResult as CheckResult;
    openResult.value = true;
  }
});

const foundResult = computed(() => (checkResult.value?.status === 'found' ? checkResult.value : null));

const noFlightsResult = computed(() => (checkResult.value?.status === 'no_flights_found' ? checkResult.value : null));

const errorResult = computed(() => (checkResult.value?.status === 'error' ? checkResult.value : null));

const formatValue = (value: string | number | null) => {
  if (value === null) return '—'
  if (typeof value === 'number') return value
  return value.replace(/,/g, ', ')
}

</script>

<template>
  <Dialog v-model:open="openResult">
    <DialogTrigger as-child>
      <!--none-->
    </DialogTrigger>
    <DialogContent class="max-h-[90vh] w-full max-w-3xl overflow-auto">
      <DialogHeader>
        <DialogTitle>Search results from Statsim</DialogTitle>
        <DialogDescription>
          <div v-if="errorResult" class="mt-4 rounded-md border border-red-300 bg-red-50 p-4 text-red-800">
            <div class="font-semibold">Error</div>
            <p class="mt-1 text-sm">{{ errorResult.message }}</p>
          </div>

          <div
            v-else-if="noFlightsResult"
            class="mt-4 rounded-md border border-yellow-300 bg-yellow-50 p-4 text-yellow-800"
          >
            <div class="font-semibold">No Flights Found</div>
            <p class="mt-1 text-sm">We could not find any flights matching this leg for the selected date.</p>
          </div>

          <div v-else-if="foundResult" class="mt-6 space-y-6 text-sm">
            <div v-if="foundResult.selected_flight">
              <h3 class="font-semibold">Selected Flight</h3>
              <div class="mt-2 space-y-2 rounded bg-muted p-3">
                <span class="font-mono">
                  {{ foundResult.selected_flight?.flight_id }}
                </span>
              </div>
            </div>

            <div>
              <h3 class="font-semibold">All Flights</h3>
              <div class="space-y-3">
                <div
                  v-for="(flight, idx) in foundResult.all_flights"
                  :key="flight.flight_id ?? `${foundResult.leg_id}-${idx}`"
                  class="rounded border p-3"
                >
                  <!-- Flight header -->
                  <div class="mb-2 flex justify-between">
                    <span>Flight ID: {{ flight.flight_id ?? '—' }}</span>
                    <span :class="flight.all_valid ? 'text-green-600' : 'text-red-600'">
                      {{ flight.all_valid ? 'VALID' : 'INVALID' }}
                    </span>
                  </div>

                  <!-- Checks -->
                  <ul class="space-y-2 text-sm">
                    <li v-for="(check, cIdx) in flight.results" :key="cIdx" class="rounded bg-muted p-2">
                      <div class="flex justify-between font-mono">
                        <span>{{ check.check }}</span>
                        <span :class="check.valid ? 'text-green-600' : 'text-red-600'">
                          {{ check.valid ? '✔' : '✘' }}
                        </span>
                      </div>

                      <div class="mt-1 grid grid-cols-2 gap-x-4 text-xs text-muted-foreground">
                        <div>
                          <span class="font-semibold">Expected:</span>
                          {{ formatValue(check.expected) }}
                        </div>
                        <div>
                          <span class="font-semibold">Actual:</span>
                          {{ formatValue(check.actual) }}
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div>
              <span class="font-semibold">Completed at:</span>
              {{ foundResult.completed_at ?? '—' }}
            </div>
          </div>
        </DialogDescription>
      </DialogHeader>
    </DialogContent>
  </Dialog>
</template>
