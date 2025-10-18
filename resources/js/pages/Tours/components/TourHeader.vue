<script setup lang="ts">
import { Tour } from '@/types';

defineProps<{
    tour: Tour
}>();
</script>

<template>
    <div class="grid md:grid-cols-3 gap-4">
        <div class="col-span-2 flex flex-col gap-2">
            <h1 class="text-3xl font-semibold">{{ tour.name }}</h1>
            <p class="text-muted-foreground">{{ tour.description }}</p>

            <div class="flex gap-4 text-sm text-muted-foreground mt-2">
                <div><strong>Begins:</strong> {{ new Date(tour.begins_at).toUTCString() }}</div>
                <div><strong>Ends:</strong> {{ new Date(tour.ends_at).toUTCString() }}</div>
            </div>

            <div class="mt-4">
                <a
                    class="text-primary underline hover:text-primary/80"
                    :href="tour.link"
                    target="_blank"
                >
                    More Info
                </a>
            </div>
        </div>

        <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70">
            <img
                v-if="tour.img_url"
                :src="tour.img_url"
                class="object-cover w-full h-full"
                alt="Event route image"
            />
        </div>
    </div>
</template>
