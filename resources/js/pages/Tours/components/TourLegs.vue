<script setup lang="ts">
import { PlaneTakeoffIcon, PlaneLandingIcon, MapIcon, CheckCircle2Icon, ClockIcon, BanIcon } from 'lucide-vue-next';
import type { Leg } from '@/types';
import LegMissing from '@/pages/Tours/components/LegMissing.vue';

defineProps<{
    legs?: Array<Leg>;
}>();


</script>

<template>
    <div class="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border bg-card/40">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <MapIcon class="w-5 h-5 text-primary" />
            <span>Route Legs</span>
        </h2>
        <!-- Legs list -->
        <div v-if="legs && legs.length > 0" class="divide-y divide-muted/30">
            <div
                v-for="(leg, index) in legs"
                :key="leg.id"
                class="flex justify-between items-center py-3"
            >
                <div class="flex flex-col gap-1">
                    <div class="text-sm text-muted-foreground">Leg {{ index + 1 }}</div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1">
                            <PlaneTakeoffIcon class="w-4 h-4 text-primary" />
                            <span class="font-medium">{{ leg.departure_icao }}</span>
                        </div>
                        <span class="text-muted-foreground">→</span>
                        <div class="flex items-center gap-1">
                            <PlaneLandingIcon class="w-4 h-4 text-primary" />
                            <span class="font-medium">{{ leg.arrival_icao }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status indicator -->
                <div class="flex items-center gap-2">
                    <template v-if="leg.status && leg.status.completed_at && leg.status.fight_data_id">
                        <CheckCircle2Icon class="w-4 h-4 text-green-500" />
                        <span class="text-sm text-green-600 dark:text-green-400">Completed</span>
                        <span class="text-sm text-muted">{{ leg.status.completed_at }}</span>
                        <span class="text-sm text-muted">#{{ leg.status.fight_data_id }}</span>
                    </template>
                    <template v-else-if="leg.status && leg.status.completed_at">
                        <CheckCircle2Icon class="w-4 h-4 text-green-500" />
                        <span class="text-sm text-green-600 dark:text-green-400">Completed</span>
                        <span class="text-sm text-muted">{{ leg.status.completed_at }}</span>
                    </template>
                    <template v-else-if="leg.status">
                        <ClockIcon class="w-4 h-4 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">Pending</span>
                        <LegMissing :leg-id="leg.id"></LegMissing>
                    </template>
                    <template v-else>
                        <BanIcon class="w-4 h-4 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">Not registered</span>
                    </template>
                </div>
            </div>
        </div>

        <div v-else class="text-muted-foreground text-sm">
            No route legs defined for this event.
        </div>
    </div>
</template>
