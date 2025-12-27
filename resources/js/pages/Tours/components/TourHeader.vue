<script setup lang="ts">
import { Tour } from '@/types';
import { router } from '@inertiajs/vue3'
import { signup, cancel } from '@/routes/tours';
import { Button } from '@/components/ui/button';
import { ref } from 'vue'

const { tour, signedUp } = defineProps<{
    tour: Tour
    signedUp: boolean
}>()

const loading = ref(false)

const signUpF = () => {
    loading.value = true
    router.visit(signup({ id: tour.id }).url)
}

const signOutF = () => {
    loading.value = true
    router.visit(cancel({ id: tour.id }).url)
}

</script>

<template>
    <div class="grid lg:grid-cols-3 gap-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-semibold">{{ tour.name }}</h1>
            <p class="text-muted-foreground">{{ tour.description }}</p>
            <a class="text-muted-foreground underline hover:text-muted-foreground/80 mt-2" :href="tour.link" target="_blank">further Information</a>

            <div class="flex gap-4 text-sm text-muted-foreground mt-2">
                <div><strong>Begins:</strong> {{ new Date(tour.begins_at).toUTCString() }}</div>
                <div><strong>Ends:</strong> {{ new Date(tour.ends_at).toUTCString() }}</div>
            </div>

            <div class="flex gap-4 text-sm text-muted-foreground mt-2">
                <div class="flex gap-4 text-sm text-muted-foreground mt-2">
                    <div v-if="signedUp">
                        <p>You are signed up!</p>
                    </div>

                    <Button
                        v-if="!signedUp"
                        :disabled="loading"
                        @click="signUpF"
                        class="flex items-center gap-2"
                    >
                        <span v-if="loading">Signing up…</span>
                        <span v-else>Sign up for this tour</span>
                    </Button>

                    <Button
                        v-if="signedUp"
                        :disabled="loading"
                        @click="signOutF"
                        class="flex items-center gap-2"
                    >
                        <span v-if="loading">Signing out…</span>
                        <span v-else>Sign out of this tour</span>
                    </Button>
                </div>
            </div>
        </div>

        <div class="col-span-2 rounded-xl border border-sidebar-border/70 overflow-hidden">
            <img
                v-if="tour.img_url"
                :src="tour.img_url"
                alt="Event route image"
                class="w-full h-auto object-contain"
            />
        </div>
    </div>
</template>
