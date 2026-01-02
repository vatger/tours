<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { cancel, signup } from '@/routes/tours';
import { Tour } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { tour, signedUp } = defineProps<{
  tour: Tour;
  signedUp: boolean;
}>();

const loading = ref(false);

const signUpF = () => {
  loading.value = true;
  router.visit(signup({ id: tour.id }).url);
};

const signOutF = () => {
  loading.value = true;
  router.visit(cancel({ id: tour.id }).url);
};
</script>

<template>
  <div class="grid gap-4 lg:grid-cols-3">
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-semibold">{{ tour.name }}</h1>
      <p class="text-muted-foreground">{{ tour.description }}</p>
      <a class="mt-2 text-muted-foreground underline hover:text-muted-foreground/80" :href="tour.link" target="_blank"
        >further Information</a
      >

      <div class="mt-2 flex gap-4 text-sm text-muted-foreground">
        <div>
          <strong>Begins:</strong>
          {{ new Date(tour.begins_at).toUTCString() }}
        </div>
        <div>
          <strong>Ends:</strong>
          {{ new Date(tour.ends_at).toUTCString() }}
        </div>
      </div>

      <div class="mt-2 flex gap-4 text-sm text-muted-foreground">
        <div class="mt-2 flex gap-4 text-sm text-muted-foreground">
          <div v-if="signedUp">
            <p>You are signed up!</p>
          </div>

          <Button v-if="!signedUp" :disabled="loading" @click="signUpF" class="flex items-center gap-2">
            <span v-if="loading">Signing up…</span>
            <span v-else>Sign up for this tour</span>
          </Button>

          <Button v-if="signedUp" :disabled="loading" @click="signOutF" class="flex items-center gap-2">
            <span v-if="loading">Signing out…</span>
            <span v-else>Sign out of this tour</span>
          </Button>
        </div>
      </div>
    </div>

    <div class="col-span-2 overflow-hidden rounded-xl border border-sidebar-border/70">
      <img v-if="tour.img_url" :src="tour.img_url" alt="Event route image" class="h-auto w-full object-contain" />
    </div>
  </div>
</template>
