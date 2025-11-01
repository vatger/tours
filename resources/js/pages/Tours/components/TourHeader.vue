<script setup lang="ts">
import { Tour } from '@/types';
import { Link } from '@inertiajs/vue3';
import { signup, cancel } from '@/routes/tours';
import { Button } from '@/components/ui/button';

defineProps<{
    tour: Tour
    signedUp: boolean
}>();
</script>

<template>
    <div class="grid md:grid-cols-3 gap-4">
        <div class="col-span-2 flex flex-col gap-2">
            <h1 class="text-3xl font-semibold">{{ tour.name }}</h1>
            <p class="text-muted-foreground">{{ tour.description }}</p>
            <Link class="text-muted-foreground underline hover:text-muted-foreground/80 mt-2" :href="tour.link" target="_blank">further Information</Link>

            <div class="flex gap-4 text-sm text-muted-foreground mt-2">
                <div><strong>Begins:</strong> {{ new Date(tour.begins_at).toUTCString() }}</div>
                <div><strong>Ends:</strong> {{ new Date(tour.ends_at).toUTCString() }}</div>
            </div>

            <div class="flex gap-4 text-sm text-muted-foreground mt-2">
            <Button v-if="!signedUp" :href="signup(tour.id)">Sign up for this tour</Button>
            <Button v-else :href="cancel(tour.id)">Sign out of this tour</Button>
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
