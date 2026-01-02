<script setup lang="ts">
import { tours } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="VATSIM Germany Tours" />

  <div class="min-h-screen bg-neutral-50 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100">
    <!-- Header -->
    <header class="mx-auto w-full max-w-6xl px-6 py-6">
      <nav class="flex justify-end">
        <Link
          v-if="$page.props.auth.user"
          :href="tours()"
          class="rounded-md border border-neutral-300 px-4 py-1.5 text-sm transition hover:border-neutral-500 dark:border-neutral-700 dark:hover:border-neutral-500"
        >
          Tours
        </Link>

        <a v-else href="/login" class="rounded-md px-4 py-1.5 text-sm transition hover:underline"> Log in </a>
      </nav>
    </header>

    <!-- Main -->
    <main class="mx-auto flex max-w-6xl px-6 pb-20">
      <section
        class="flex w-full flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-black/10 lg:flex-row dark:bg-neutral-900 dark:ring-white/10"
      >
        <!-- Banner -->
        <div class="relative h-56 w-full lg:h-auto lg:w-1/2">
          <img
            src="/img/img.png"
            alt="VATSIM Germany Tours Banner"
            class="absolute inset-0 h-full w-full object-cover"
          />
          <!-- subtle overlay -->
          <div class="absolute inset-0 bg-gradient-to-tr from-black/40 via-black/10 to-transparent" />
        </div>

        <!-- Content -->
        <div class="flex flex-1 flex-col justify-center gap-4 p-8 lg:p-16">
          <h1 class="text-xl font-semibold tracking-tight">VATSIM Germany Tours</h1>

          <p class="max-w-prose text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">
            Das Tourensystem wird derzeit überarbeitet. Die angezeigten Daten müssen nicht korrekt sein. Bitte keine
            Fehlermeldungen an den Support senden.
          </p>
        </div>
      </section>
    </main>
  </div>
</template>
