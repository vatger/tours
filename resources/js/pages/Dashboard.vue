<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, tours } from '@/routes';
import type { BreadcrumbItem, Tour } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Award, Circle, CircleCheck, CircleCheckBig, CirclePause, Compass, MapPinned, PlaneLanding, PlaneTakeoff, Radio, Users } from 'lucide-vue-next';
import { computed } from 'vue';

interface CommunityStats {
  legsFlownLast30Days: number;
  tourRegistrations: number;
  badgesAwarded: number;
  participatingPilots: number;
}

interface PersonalStats {
  legsFlown: number;
  activeTours: number;
  badgesEarned: number;
}

interface LegSummary {
  id: number;
  tourId: number;
  tourName: string;
  departureIcao: string;
  arrivalIcao: string;
  completedAt: string | null;
}

interface NetworkFlight {
  id: number | null;
  callsign: string | null;
  departureIcao: string | null;
  arrivalIcao: string | null;
  aircraft: string | null;
  flightType: string | null;
  departedAt: string | null;
  arrivedAt: string | null;
}

const page = usePage();
const allTours = computed(() => page.props.tours_list as Tour[]);
const communityStats = computed(() => page.props.communityStats as CommunityStats);
const personalStats = computed(() => page.props.personalStats as PersonalStats);
const recentLegs = computed(() => page.props.recentLegs as LegSummary[]);
const nextLegs = computed(() => page.props.nextLegs as LegSummary[]);
const recentNetworkFlights = computed(() => page.props.recentNetworkFlights as NetworkFlight[]);
const primaryTourUrl = computed(() => (allTours.value[0] ? tours({ id: allTours.value[0].id }) : dashboard()));

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Overview', href: dashboard().url }];

const sidebarItems = computed(() =>
  allTours.value.map((tour) => ({
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
  })),
);

const formatDate = (date: string | null) =>
  date
    ? new Intl.DateTimeFormat(undefined, { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(date))
    : '';

const formatDateTime = (date: string | null) =>
  date
    ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date))
    : 'Not available';
</script>

<template>
  <Head title="Overview" />

  <AppLayout :breadcrumbs="breadcrumbs" :sidebar-items="sidebarItems">
    <main class="mx-auto w-full max-w-7xl space-y-8 p-4 md:p-6">
      <section class="flex flex-col justify-between gap-5 rounded-2xl border border-border bg-card p-6 shadow-sm sm:flex-row sm:items-end">
        <div>
          <p class="text-sm font-medium text-ring">VATGER Tours</p>
          <h1 class="mt-1 text-3xl font-semibold tracking-tight text-primary">Your flying overview</h1>
          <p class="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">
            Keep track of your progress and see what the tour community has been flying.
          </p>
        </div>
        <Link
          :href="primaryTourUrl"
          class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
        >
          <Compass class="size-4" />
          Browse tours
        </Link>
      </section>

      <section>
        <div class="mb-3 flex items-center gap-2">
          <Users class="size-5 text-ring" />
          <div>
            <h2 class="font-semibold text-primary">Community activity</h2>
            <p class="text-sm text-muted-foreground">What pilots have achieved across all tours.</p>
          </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <PlaneTakeoff class="size-5 text-ring" />
            <p class="mt-4 text-3xl font-semibold text-primary">{{ communityStats.legsFlownLast30Days }}</p>
            <p class="mt-1 text-sm font-medium">Legs flown</p>
            <p class="mt-1 text-xs text-muted-foreground">In the last 30 days</p>
          </article>
          <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <MapPinned class="size-5 text-ring" />
            <p class="mt-4 text-3xl font-semibold text-primary">{{ communityStats.tourRegistrations }}</p>
            <p class="mt-1 text-sm font-medium">Tour registrations</p>
            <p class="mt-1 text-xs text-muted-foreground">Across all available tours</p>
          </article>
          <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <Award class="size-5 text-ring" />
            <p class="mt-4 text-3xl font-semibold text-primary">{{ communityStats.badgesAwarded }}</p>
            <p class="mt-1 text-sm font-medium">Badges awarded</p>
            <p class="mt-1 text-xs text-muted-foreground">Recognitions given to pilots</p>
          </article>
          <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <Users class="size-5 text-ring" />
            <p class="mt-4 text-3xl font-semibold text-primary">{{ communityStats.participatingPilots }}</p>
            <p class="mt-1 text-sm font-medium">Participating pilots</p>
            <p class="mt-1 text-xs text-muted-foreground">Registered for at least one tour</p>
          </article>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
          <div>
            <div class="mb-3 flex items-center gap-2">
              <PlaneTakeoff class="size-5 text-ring" />
              <div>
                <h2 class="font-semibold text-primary">Your progress</h2>
                <p class="text-sm text-muted-foreground">A quick look at your tour history.</p>
              </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
              <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <p class="text-3xl font-semibold text-primary">{{ personalStats.legsFlown }}</p>
                <p class="mt-1 text-sm text-muted-foreground">Legs completed</p>
              </article>
              <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <p class="text-3xl font-semibold text-primary">{{ personalStats.activeTours }}</p>
                <p class="mt-1 text-sm text-muted-foreground">Active tours</p>
              </article>
              <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <p class="text-3xl font-semibold text-primary">{{ personalStats.badgesEarned }}</p>
                <p class="mt-1 text-sm text-muted-foreground">Badges earned</p>
              </article>
            </div>
          </div>

          <section class="rounded-xl border border-border bg-card shadow-sm">
            <div class="border-b border-border px-5 py-4">
              <h2 class="font-semibold text-primary">Last legs flown</h2>
              <p class="mt-1 text-sm text-muted-foreground">Your most recently completed tour legs.</p>
            </div>
            <div v-if="recentLegs.length" class="divide-y divide-border">
              <Link
                v-for="leg in recentLegs"
                :key="leg.id"
                :href="tours({ id: leg.tourId })"
                class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-muted/50"
              >
                <div>
                  <p class="font-medium">{{ leg.departureIcao }} → {{ leg.arrivalIcao }}</p>
                  <p class="mt-0.5 text-sm text-muted-foreground">{{ leg.tourName }}</p>
                </div>
                <time class="shrink-0 text-xs text-muted-foreground">{{ formatDate(leg.completedAt) }}</time>
              </Link>
            </div>
            <p v-else class="px-5 py-8 text-sm text-muted-foreground">No tour legs completed yet. Your flights will appear here once verified.</p>
          </section>
        </div>

        <section class="rounded-xl border border-border bg-card shadow-sm">
          <div class="border-b border-border px-5 py-4">
            <h2 class="font-semibold text-primary">Next legs to fly</h2>
            <p class="mt-1 text-sm text-muted-foreground">Continue with one of your active tours.</p>
          </div>
          <div v-if="nextLegs.length" class="divide-y divide-border">
            <Link
              v-for="leg in nextLegs"
              :key="leg.id"
              :href="tours({ id: leg.tourId })"
              class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-muted/50"
            >
              <div>
                <p class="font-medium">{{ leg.departureIcao }} → {{ leg.arrivalIcao }}</p>
                <p class="mt-0.5 text-sm text-muted-foreground">{{ leg.tourName }}</p>
              </div>
              <span class="rounded-full bg-secondary px-2.5 py-1 text-xs font-medium text-secondary-foreground">Ready to fly</span>
            </Link>
          </div>
          <div v-else class="px-5 py-8 text-sm text-muted-foreground">
            <p>You have no upcoming tour legs right now.</p>
            <Link :href="primaryTourUrl" class="mt-3 inline-flex font-medium text-ring hover:underline">Explore available tours</Link>
          </div>
        </section>
      </section>

      <section class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
        <div class="flex flex-col gap-2 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="flex items-center gap-2 font-semibold text-primary"><Radio class="size-5 text-ring" /> Latest network flights</h2>
            <p class="mt-1 text-sm text-muted-foreground">Your five most recent flights on the network, including flights outside tours.</p>
          </div>
          <span class="text-xs text-muted-foreground">Updated every 5 minutes</span>
        </div>
        <div v-if="recentNetworkFlights.length" class="divide-y divide-border">
          <article v-for="flight in recentNetworkFlights" :key="flight.id ?? `${flight.callsign}-${flight.departedAt}`" class="grid gap-3 px-5 py-4 sm:grid-cols-[1fr_auto] sm:items-center">
            <div>
              <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="font-semibold">{{ flight.departureIcao ?? '—' }} → {{ flight.arrivalIcao ?? '—' }}</span>
                <span v-if="flight.flightType" class="rounded-full bg-secondary px-2 py-0.5 text-xs font-medium text-secondary-foreground">{{ flight.flightType }}</span>
              </div>
              <p class="mt-1 text-sm text-muted-foreground">
                {{ flight.callsign ?? 'Unknown callsign' }}<span v-if="flight.aircraft"> · {{ flight.aircraft }}</span>
              </p>
            </div>
            <div class="grid gap-1 text-xs text-muted-foreground sm:text-right">
              <span class="inline-flex items-center gap-1 sm:justify-end"><PlaneTakeoff class="size-3.5" /> {{ formatDateTime(flight.departedAt) }}</span>
              <span class="inline-flex items-center gap-1 sm:justify-end"><PlaneLanding class="size-3.5" /> {{ formatDateTime(flight.arrivedAt) }}</span>
            </div>
          </article>
        </div>
        <div v-else class="px-5 py-8 text-sm text-muted-foreground">No recent network flights are available.</div>
      </section>
    </main>
  </AppLayout>
</template>
