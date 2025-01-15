<!-- AuthSimpleLayout.vue -->
<script setup lang="ts">
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

interface Props {
    title?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: undefined,
    description: undefined,
});
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col items-center gap-2">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md">
                            <ApplicationLogo class="size-10 fill-current text-black dark:text-white" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <h1 class="text-xl font-bold">{{ title }}</h1>
                    <p class="text-center text-sm">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
