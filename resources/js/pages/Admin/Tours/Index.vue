<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Tour } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CalendarDays, Clock3, MapPin, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{ tours: (Tour & { legs_count: number })[] }>();
const page = usePage();
const flash = computed(() => (page.props.flash as { success?: string } | undefined)?.success);
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Manage tours', href: '/admin/tours' }];

const date = (value: string) =>
  new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
const remove = (tour: Tour) => {
  if (window.confirm(`Delete “${tour.name}”? This also removes its legs.`)) {
    router.delete(`/admin/tours/${tour.id}`);
  }
};
</script>

<template>
  <Head title="Manage tours" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="mx-auto flex w-full max-w-6xl flex-col gap-8 p-6 lg:p-10">
      <section class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Admin workspace</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-tight">Manage tours</h1>
          <p class="mt-2 max-w-xl text-muted-foreground">
            Create and maintain the routes shown on the public tour dashboard.
          </p>
        </div>
        <Button as-child
          ><Link href="/admin/tours/create"><Plus class="mr-2 size-4" />New tour</Link></Button
        >
      </section>

      <div v-if="flash" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ flash }}
      </div>

      <section v-if="props.tours.length" class="grid gap-4">
        <article
          v-for="tour in props.tours"
          :key="tour.id"
          class="rounded-xl border bg-card p-5 shadow-xs transition-shadow hover:shadow-sm"
        >
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 gap-4">
              <img v-if="tour.img_url" :src="tour.img_url" :alt="tour.name" class="size-16 rounded-lg object-cover" />
              <div class="min-w-0">
                <h2 class="truncate text-lg font-semibold">{{ tour.name }}</h2>
                <p class="mt-1 line-clamp-2 text-sm text-muted-foreground">{{ tour.description }}</p>
              </div>
            </div>
            <div class="flex gap-2">
              <Button variant="outline" size="sm" as-child
                ><Link :href="`/admin/tours/${tour.id}/edit`"><Pencil class="mr-2 size-3.5" />Edit</Link></Button
              >
              <Button
                variant="ghost"
                size="icon"
                class="text-destructive hover:text-destructive"
                title="Delete tour"
                @click="remove(tour)"
                ><Trash2 class="size-4"
              /></Button>
            </div>
          </div>
          <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-t pt-4 text-sm text-muted-foreground">
            <span class="inline-flex items-center gap-2"
              ><CalendarDays class="size-4" />{{ date(tour.begins_at) }}</span
            >
            <span class="inline-flex items-center gap-2"><Clock3 class="size-4" />until {{ date(tour.ends_at) }}</span>
            <span class="inline-flex items-center gap-2"
              ><MapPin class="size-4" />{{ tour.legs_count }} {{ tour.legs_count === 1 ? 'leg' : 'legs' }}</span
            >
          </div>
        </article>
      </section>
      <section v-else class="rounded-xl border border-dashed p-12 text-center">
        <h2 class="text-lg font-semibold">No tours yet</h2>
        <p class="mt-2 text-muted-foreground">Create the first tour to start building your schedule.</p>
        <Button as-child class="mt-6"
          ><Link href="/admin/tours/create"><Plus class="mr-2 size-4" />Create a tour</Link></Button
        >
      </section>
    </main>
  </AppLayout>
</template>
