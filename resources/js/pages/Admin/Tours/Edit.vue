<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Leg, Tour } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowDown, ArrowLeft, ArrowUp, GripVertical, Plus, Save, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{ tour: (Tour & { legs: Leg[] }) | null; isActive?: boolean }>();
const editing = computed(() => Boolean(props.tour));
const editMode = ref(!props.isActive);
const canEdit = computed(() => editMode.value || !props.isActive);
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Manage tours', href: '/admin/tours' },
  { title: editing.value ? 'Edit tour' : 'New tour', href: '#' },
]);
const toInput = (value?: string) => (value ? new Date(value).toISOString().slice(0, 16) : '');
const setFile = (field: 'tour_image' | 'badge_image', event: Event) => {
  const input = event.target as HTMLInputElement;
  form[field] = input.files?.[0] ?? null;
};
const form = useForm({
  name: props.tour?.name ?? '',
  description: props.tour?.description ?? '',
  link: props.tour?.link ?? '',
  tour_image: null as File | null,
  badge_image: null as File | null,
  aircraft: props.tour?.aircraft ?? '',
  flight_rules: props.tour?.flight_rules ?? '',
  require_order: props.tour?.require_order ?? false,
  begins_at: toInput(props.tour?.begins_at),
  ends_at: toInput(props.tour?.ends_at),
  forum_badge_id: props.tour?.forum_badge_id ?? '',
  legs: (props.tour?.legs ?? []).map((leg) => ({
    id: leg.id as number | null,
    departure_icao: leg.departure_icao,
    arrival_icao: leg.arrival_icao,
    completed_users_count: leg.completed_users_count ?? 0,
  })),
});
const addLeg = () =>
  form.legs.push({ id: null as number | null, departure_icao: '', arrival_icao: '', completed_users_count: 0 });
const removeLeg = (index: number) => form.legs.splice(index, 1);
const moveLeg = (index: number, direction: -1 | 1) => {
  const nextIndex = index + direction;
  if (nextIndex < 0 || nextIndex >= form.legs.length) return;
  const [leg] = form.legs.splice(index, 1);
  form.legs.splice(nextIndex, 0, leg);
};
const submit = () =>
  form
    .transform((data) => ({ ...data, _method: editing.value ? 'put' : undefined }))
    .post(editing.value ? `/admin/tours/${props.tour!.id}` : '/admin/tours');
</script>

<template>
  <Head :title="editing ? `Edit ${props.tour?.name}` : 'New tour'" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 p-6 lg:p-10">
      <div class="flex items-start gap-4">
        <Button variant="outline" size="icon" as-child
          ><Link href="/admin/tours" aria-label="Back to tours"><ArrowLeft class="size-4" /></Link
        ></Button>
        <div>
          <p class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Admin workspace</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ editing ? 'Edit tour' : 'New tour' }}</h1>
          <p class="mt-2 text-muted-foreground">Set the schedule, rules, and route legs for this tour.</p>
        </div>
      </div>

      <section
        v-if="props.isActive"
        class="flex gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-950 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-100"
        role="status"
      >
        <span class="mt-0.5 text-lg" aria-hidden="true">⚠</span>
        <div>
          <h2 class="font-semibold">This tour is currently active</h2>
          <p class="mt-1 text-sm text-amber-900/80 dark:text-amber-100/80">
            Review the current route and participant progress below. Enable edit mode when you want to add, remove, or
            reorder legs. Changes affect participants immediately; progress for legs that remain in the tour is
            preserved.
          </p>
          <Button v-if="!editMode" type="button" variant="outline" size="sm" class="mt-3" @click="editMode = true"
            >Enable edit mode</Button
          >
        </div>
      </section>

      <form class="grid gap-6" @submit.prevent="submit">
        <section class="rounded-xl border bg-card p-6 shadow-xs">
          <h2 class="text-lg font-semibold">Tour details</h2>
          <div class="mt-5 grid gap-5 md:grid-cols-2">
            <label class="grid gap-2 md:col-span-2"
              ><span class="text-sm font-medium">Name <span class="text-destructive">*</span></span
              ><Input v-model="form.name" required :disabled="!canEdit" placeholder="European Coastal Tour" /><small
                v-if="form.errors.name"
                class="text-destructive"
                >{{ form.errors.name }}</small
              ></label
            >
            <label class="grid gap-2 md:col-span-2"
              ><span class="text-sm font-medium">Description <span class="text-destructive">*</span></span
              ><textarea
                v-model="form.description"
                required
                :disabled="!canEdit"
                rows="4"
                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                placeholder="What makes this tour special?"
              /><small v-if="form.errors.description" class="text-destructive">{{
                form.errors.description
              }}</small></label
            >
            <label class="grid gap-2"
              ><span class="text-sm font-medium">Tour link <span class="text-destructive">*</span></span
              ><Input v-model="form.link" required :disabled="!canEdit" type="url" placeholder="https://…"
            /></label>
            <label class="grid gap-2"
              ><span class="text-sm font-medium">Aircraft <span class="text-destructive">*</span></span
              ><Input v-model="form.aircraft" required :disabled="!canEdit" placeholder="A320, B738" /><span
                class="text-xs text-muted-foreground"
                >Comma-separated prefixes accepted.</span
              ></label
            >
            <label class="grid gap-2 md:col-span-2"
              ><span class="text-sm font-medium"
                >Tour image <span v-if="!props.tour?.img_url" class="text-destructive">*</span></span
              ><input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                :required="!props.tour?.img_url"
                :disabled="!canEdit"
                class="block w-full rounded-md border border-input text-sm file:mr-4 file:border-0 file:bg-primary file:px-4 file:py-2 file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                @change="setFile('tour_image', $event)"
              /><span class="text-xs text-muted-foreground"
                >Exactly 1200×675 pixels (16:9). Stored locally and published through a static link.</span
              ><a
                v-if="props.tour?.img_url"
                :href="props.tour.img_url"
                target="_blank"
                class="text-xs text-primary underline"
                >Open current tour image</a
              ></label
            >
            <label class="grid gap-2 md:col-span-2"
              ><span class="text-sm font-medium"
                >Forum badge <span v-if="!props.tour?.badge_img_url" class="text-destructive">*</span></span
              ><input
                type="file"
                accept="image/png"
                :required="!props.tour?.badge_img_url"
                :disabled="!canEdit"
                class="block w-full rounded-md border border-input text-sm file:mr-4 file:border-0 file:bg-primary file:px-4 file:py-2 file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                @change="setFile('badge_image', $event)"
              /><span class="text-xs text-muted-foreground"
                >PNG only, exactly 300×300 pixels. Stored locally and published through a static link.</span
              ><a
                v-if="props.tour?.badge_img_url"
                :href="props.tour.badge_img_url"
                target="_blank"
                class="text-xs text-primary underline"
                >Open current forum badge</a
              ></label
            >
            <label class="grid gap-2"
              ><span class="text-sm font-medium">Flight rules</span
              ><select
                v-model="form.flight_rules"
                :disabled="!canEdit"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <option value="">Any flight rules</option>
                <option value="i">IFR — Instrument Flight Rules</option>
                <option value="v">VFR — Visual Flight Rules</option>
              </select></label
            >
            <label class="grid gap-2"
              ><span class="text-sm font-medium">Begins at <span class="text-destructive">*</span></span
              ><Input v-model="form.begins_at" required :disabled="!canEdit" type="datetime-local"
            /></label>
            <label class="grid gap-2"
              ><span class="text-sm font-medium">Ends at <span class="text-destructive">*</span></span
              ><Input v-model="form.ends_at" required :disabled="!canEdit" type="datetime-local"
            /></label>
            <label class="flex items-center gap-3 md:col-span-2"
              ><input
                v-model="form.require_order"
                :disabled="!canEdit"
                type="checkbox"
                class="size-4 rounded border-input accent-[var(--primary)]"
              /><span
                ><span class="block text-sm font-medium">Require legs in order</span
                ><span class="text-xs text-muted-foreground"
                  >Pilots must complete each leg before starting the next one.</span
                ></span
              ></label
            >
          </div>
        </section>

        <section class="rounded-xl border bg-card p-6 shadow-xs">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold">Route legs</h2>
              <p class="mt-1 text-sm text-muted-foreground">Add each departure and arrival pair in order.</p>
            </div>
            <Button type="button" variant="outline" size="sm" :disabled="!canEdit" @click="addLeg"
              ><Plus class="mr-2 size-4" />Add leg</Button
            >
          </div>
          <div v-if="form.legs.length" class="mt-5 grid gap-3">
            <div
              v-for="(leg, index) in form.legs"
              :key="leg.id ?? `new-${index}`"
              class="flex items-center gap-3 rounded-lg border bg-muted/20 p-3"
            >
              <GripVertical class="hidden size-4 text-muted-foreground sm:block" /><span
                class="w-6 text-sm font-medium text-muted-foreground"
                >{{ index + 1 }}</span
              ><span class="flex shrink-0 gap-1">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="size-7"
                  :disabled="!canEdit || index === 0"
                  title="Move leg up"
                  @click="moveLeg(index, -1)"
                  ><ArrowUp class="size-3.5"
                /></Button>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="size-7"
                  :disabled="!canEdit || index === form.legs.length - 1"
                  title="Move leg down"
                  @click="moveLeg(index, 1)"
                  ><ArrowDown class="size-3.5"
                /></Button> </span
              ><Input
                v-model="leg.departure_icao"
                required
                :disabled="!canEdit"
                class="uppercase"
                maxlength="4"
                placeholder="From (ICAO)"
              /><span class="text-destructive" aria-hidden="true">*</span><span class="text-muted-foreground">→</span
              ><Input
                v-model="leg.arrival_icao"
                required
                :disabled="!canEdit"
                class="uppercase"
                maxlength="4"
                placeholder="To (ICAO)"
              /><span class="text-destructive" aria-hidden="true">*</span
              ><span v-if="props.isActive && leg.id" class="ml-auto text-xs whitespace-nowrap text-muted-foreground"
                >{{ leg.completed_users_count ?? 0 }} completed</span
              ><Button
                type="button"
                variant="ghost"
                size="icon"
                class="text-destructive hover:text-destructive"
                :disabled="!canEdit"
                title="Remove leg"
                @click="removeLeg(index)"
                ><Trash2 class="size-4"
              /></Button>
            </div>
          </div>
          <div v-else class="mt-5 rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
            No legs added yet.
          </div>
        </section>

        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" as-child><Link href="/admin/tours">Cancel</Link></Button
          ><Button type="submit" :disabled="form.processing || !canEdit"
            ><Save class="mr-2 size-4" />{{ form.processing ? 'Saving…' : 'Save tour' }}</Button
          >
        </div>
      </form>
    </main>
  </AppLayout>
</template>
