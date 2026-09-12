<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Search, Users } from 'lucide-vue-next';

const props = defineProps<{ searchedId?: number | null; notFound?: boolean }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users & progress', href: '/admin/users' }];
const form = useForm({ vatsim_id: props.searchedId?.toString() ?? '' });
</script>

<template>
  <Head title="Users & progress" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 p-6 lg:p-10">
      <section>
        <p class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Admin workspace</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Users & progress</h1>
        <p class="mt-2 max-w-xl text-muted-foreground">
          Search for one user by VATSIM ID to view their enrolled tours and leg progress.
        </p>
      </section>

      <section class="max-w-xl rounded-xl border bg-card p-6 shadow-xs">
        <div class="flex items-center gap-3">
          <span class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary"
            ><Users class="size-5"
          /></span>
          <div>
            <h2 class="font-semibold">Find a user</h2>
            <p class="text-sm text-muted-foreground">Only the VATSIM ID is required.</p>
          </div>
        </div>
        <form class="mt-6 flex gap-3" @submit.prevent="form.get('/admin/users')">
          <Input
            v-model="form.vatsim_id"
            type="number"
            min="1"
            required
            placeholder="VATSIM ID"
            aria-label="VATSIM ID"
          />
          <Button type="submit" :disabled="form.processing"><Search class="mr-2 size-4" />Search</Button>
        </form>
        <p v-if="props.notFound" class="mt-4 text-sm text-muted-foreground">No user found for that VATSIM ID.</p>
      </section>
    </main>
  </AppLayout>
</template>
