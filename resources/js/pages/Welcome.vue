<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { login, tours } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Award, CheckCircle2, MapPinned, PlaneTakeoff, Route as RouteIcon } from 'lucide-vue-next';

const steps = [
  {
    number: '01',
    title: 'Choose a tour',
    description: 'Pick a route that matches your aircraft, flight rules, and ambitions.',
    icon: MapPinned,
  },
  {
    number: '02',
    title: 'Fly every leg',
    description: 'Complete each sector on the VATSIM network at your own pace.',
    icon: PlaneTakeoff,
  },
  {
    number: '03',
    title: 'Track your progress',
    description: 'Your flights are checked against the tour requirements automatically.',
    icon: CheckCircle2,
  },
  {
    number: '04',
    title: 'Earn the badge',
    description: 'Finish the route and add the division tour badge to your achievements.',
    icon: Award,
  },
];
</script>

<template>
  <Head title="VATGER Tours" />

  <div class="relative min-h-screen overflow-hidden bg-background text-foreground">
    <div class="absolute inset-x-0 top-0 h-1 bg-ring" aria-hidden="true"></div>

    <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-6 sm:px-8">
      <Link href="/" aria-label="VATGER Tours home" class="block">
        <AppLogoIcon class="h-10 w-52 sm:h-12 sm:w-64" />
      </Link>

      <Link
        v-if="$page.props.auth.user"
        :href="tours()"
        class="inline-flex h-10 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
      >
        Open tours
        <ArrowRight class="size-4" />
      </Link>
      <Link
        v-else
        :href="login()"
        class="inline-flex h-10 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
      >
        Log in
        <ArrowRight class="size-4" />
      </Link>
    </header>

    <main>
      <section
        class="mx-auto grid w-full max-w-7xl gap-12 px-5 pt-14 pb-20 sm:px-8 sm:pt-20 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-20 lg:pt-28 lg:pb-28"
      >
        <div>
          <div
            class="mb-6 inline-flex items-center gap-2 rounded-full border border-border bg-card px-3 py-1 text-xs font-medium text-muted-foreground"
          >
            <span class="size-1.5 rounded-full bg-ring"></span>
            VATSIM Germany Division
          </div>

          <h1
            class="max-w-4xl text-5xl leading-[0.98] font-semibold tracking-[-0.045em] text-primary sm:text-6xl lg:text-7xl"
          >
            Fly the route.<br />
            <span class="text-ring">Complete the tour.</span>
          </h1>

          <p class="mt-7 max-w-2xl text-lg leading-relaxed text-muted-foreground sm:text-xl">
            Explore curated routes across Germany and beyond. Fly each leg on VATSIM, follow your progress, and earn
            recognition for completing the journey.
          </p>

          <div class="mt-9 flex flex-col gap-3 sm:flex-row">
            <Link
              v-if="$page.props.auth.user"
              :href="tours()"
              class="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
            >
              Browse tours
              <ArrowRight class="size-4" />
            </Link>
            <Link
              v-else
              :href="login()"
              class="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
            >
              Continue with VATSIM
              <ArrowRight class="size-4" />
            </Link>
            <a
              href="#how-it-works"
              class="inline-flex h-12 items-center justify-center rounded-md border border-border bg-card px-6 text-sm font-medium transition-colors hover:bg-accent"
            >
              How it works
            </a>
          </div>
        </div>

        <div id="how-it-works" class="rounded-2xl border border-border bg-card p-5 shadow-sm sm:p-7">
          <div class="mb-5 flex items-center justify-between border-b border-border pb-5">
            <div>
              <p class="text-xs font-medium tracking-[0.16em] text-muted-foreground uppercase">Your journey</p>
              <h2 class="mt-1 text-xl font-semibold text-primary">From briefing to badge</h2>
            </div>
            <RouteIcon class="size-6 text-ring" />
          </div>

          <ol class="space-y-1">
            <li
              v-for="step in steps"
              :key="step.number"
              class="group grid grid-cols-[2rem_2.5rem_1fr] items-start gap-3 rounded-xl px-2 py-3 transition-colors hover:bg-muted/60"
            >
              <span class="pt-1 text-xs font-medium text-muted-foreground">{{ step.number }}</span>
              <span class="flex size-10 items-center justify-center rounded-lg bg-secondary text-primary">
                <component :is="step.icon" class="size-4" />
              </span>
              <span>
                <span class="block font-medium text-foreground">{{ step.title }}</span>
                <span class="mt-0.5 block text-sm leading-relaxed text-muted-foreground">{{ step.description }}</span>
              </span>
            </li>
          </ol>
        </div>
      </section>

      <section class="border-y border-border bg-card/55">
        <div
          class="mx-auto grid w-full max-w-7xl divide-y divide-border px-5 sm:px-8 md:grid-cols-3 md:divide-x md:divide-y-0"
        >
          <article class="py-8 md:pr-8">
            <p class="text-sm font-semibold text-ring">Curated routes</p>
            <h2 class="mt-2 text-xl font-semibold text-primary">More than a destination</h2>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
              Every tour is a purposeful sequence of legs with clear requirements and a finish line.
            </p>
          </article>
          <article class="py-8 md:px-8">
            <p class="text-sm font-semibold text-ring">Flexible progress</p>
            <h2 class="mt-2 text-xl font-semibold text-primary">Fly on your schedule</h2>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
              Return whenever you like and continue from the exact point where you left off.
            </p>
          </article>
          <article class="py-8 md:pl-8">
            <p class="text-sm font-semibold text-ring">Automatic checks</p>
            <h2 class="mt-2 text-xl font-semibold text-primary">Less admin, more flying</h2>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
              Tour legs are matched with your network activity so progress stays clear and current.
            </p>
          </article>
        </div>
      </section>

      <section class="mx-auto w-full max-w-7xl px-5 py-12 sm:px-8">
        <div
          class="flex flex-col gap-3 rounded-xl border border-border bg-muted/60 px-5 py-4 text-sm sm:flex-row sm:items-center sm:justify-between"
        >
          <p class="text-muted-foreground">
            <strong class="font-semibold text-foreground">Development notice:</strong>
            The tour system is still being refined, so some displayed data may be incomplete.
          </p>
          <span class="shrink-0 text-xs font-medium tracking-wide text-muted-foreground uppercase">Public preview</span>
        </div>
      </section>
    </main>

    <footer
      class="mx-auto flex w-full max-w-7xl flex-col gap-3 border-t border-border px-5 py-7 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:px-8"
    >
      <p>VATGER Tours · VATSIM Germany</p>
      <a
        href="https://vatsim-germany.org"
        class="transition-colors hover:text-foreground"
        target="_blank"
        rel="noreferrer"
      >
        vatsim-germany.org
      </a>
    </footer>
  </div>
</template>
