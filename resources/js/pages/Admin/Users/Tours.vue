<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BadgeCheck, Check, Circle } from 'lucide-vue-next';

interface UserTourLeg {
  id: number;
  departure_icao: string;
  arrival_icao: string;
  completed_at: string | null;
  quick_stats_flight_id: number | null;
  statsim_flight_id: number | null;
}

interface UserTour {
  id: number;
  name: string;
  begins_at: string;
  ends_at: string;
  completed: boolean;
  badge_given: boolean;
  signup: {
    id: number;
    user_id: number;
    tour_id: number;
    completed: boolean;
    badge_given: boolean;
  };
  legs: UserTourLeg[];
}

const props = defineProps<{
  user: { id: number };
  tours: UserTour[];
}>();
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users & progress', href: '/admin/users' },
  { title: `User #${props.user.id}`, href: '#' },
];
const date = (value: string) => new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value));
const completedLegs = (tour: UserTour) => tour.legs.filter((leg) => leg.completed_at).length;
</script>

<template>
  <Head :title="`User #${props.user.id} — tours`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 p-6 lg:p-10">
      <div class="flex items-start gap-4">
        <Button variant="outline" size="icon" as-child
          ><Link href="/admin/users" aria-label="Back to users"><ArrowLeft class="size-4" /></Link
        ></Button>
        <div>
          <p class="text-sm font-medium tracking-wide text-muted-foreground uppercase">User progress</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-tight">User #{{ props.user.id }}</h1>
          <p class="mt-2 text-muted-foreground">Tours this user has joined and the legs they have completed.</p>
        </div>
      </div>

      <section v-if="props.tours.length" class="grid gap-5">
        <article v-for="tour in props.tours" :key="tour.id" class="rounded-xl border bg-card p-6 shadow-xs">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold">{{ tour.name }}</h2>
              <p class="mt-1 text-sm text-muted-foreground">{{ date(tour.begins_at) }} – {{ date(tour.ends_at) }}</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-medium">
              <span class="rounded-full bg-muted px-3 py-1"
                >{{ completedLegs(tour) }}/{{ tour.legs.length }} legs completed</span
              >
              <span
                v-if="tour.completed"
                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-green-800"
                ><Check class="size-3.5" />Tour completed</span
              >
              <span
                v-if="tour.badge_given"
                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-amber-800"
                ><BadgeCheck class="size-3.5" />Badge given</span
              >
            </div>
          </div>

          <dl class="mt-4 grid gap-2 border-t pt-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <dt class="text-muted-foreground">Signup ID</dt>
              <dd class="font-medium">{{ tour.signup.id }}</dd>
            </div>
            <div>
              <dt class="text-muted-foreground">User ID</dt>
              <dd class="font-medium">{{ tour.signup.user_id }}</dd>
            </div>
            <div>
              <dt class="text-muted-foreground">Tour ID</dt>
              <dd class="font-medium">{{ tour.signup.tour_id }}</dd>
            </div>
            <div>
              <dt class="text-muted-foreground">Badge given</dt>
              <dd class="font-medium">{{ tour.signup.badge_given ? 'Yes' : 'No' }}</dd>
            </div>
          </dl>

          <div v-if="tour.legs.length" class="mt-5 grid gap-2 border-t pt-4">
            <div
              v-for="(leg, index) in tour.legs"
              :key="leg.id"
              class="flex items-center gap-3 rounded-lg bg-muted/30 px-3 py-2 text-sm"
            >
              <Check v-if="leg.completed_at" class="size-4 text-green-600" />
              <Circle v-else class="size-3.5 text-muted-foreground" />
              <span class="w-5 text-muted-foreground">{{ index + 1 }}</span>
              <span class="font-medium">{{ leg.departure_icao }}</span
              ><span class="text-muted-foreground">→</span><span class="font-medium">{{ leg.arrival_icao }}</span>
              <span v-if="leg.completed_at" class="ml-auto text-xs text-muted-foreground">{{
                date(leg.completed_at)
              }}</span>
              <span v-else class="ml-auto text-xs text-muted-foreground">Not completed</span>
              <a
                v-if="leg.completed_at && (leg.quick_stats_flight_id || leg.statsim_flight_id)"
                :href="`/admin/users/${props.user.id}/tours/${tour.id}/legs/${leg.id}/flight`"
                target="_blank"
                rel="noopener"
                class="text-xs font-medium text-primary underline"
                >Flight details (Quick Stats #{{ leg.quick_stats_flight_id ?? '—' }} · Statsim #{{
                  leg.statsim_flight_id ?? '—'
                }})</a
              >
            </div>
          </div>
          <p v-else class="mt-5 border-t pt-4 text-sm text-muted-foreground">This tour has no legs.</p>
        </article>
      </section>
      <section v-else class="rounded-xl border border-dashed p-12 text-center">
        <h2 class="text-lg font-semibold">No tours joined</h2>
        <p class="mt-2 text-muted-foreground">This user has not enrolled in any tours yet.</p>
      </section>
    </main>
  </AppLayout>
</template>
