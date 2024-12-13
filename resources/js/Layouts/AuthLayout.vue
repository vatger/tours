<script setup lang="ts">
const props = defineProps(['name', 'quote'])
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
// import "@/app.css";
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="w-full relative h-dvh flex-col items-center grid lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="absolute right-4 top-4 md:right-8 md:top-8">
            <slot name="topRight"></slot>
        </div>
        <div class="relative h-full flex-col items-start bg-muted p-10 text-white dark:border-r hidden lg:flex">
            <div class="absolute inset-0 bg-zinc-900" />
            <a :href="route('home')" class="relative z-50 inline-flex items-center text-lg font-medium cursor-pointer">
                <ApplicationLogo class="text-white w-6 h-6 mr-2" />
                {{  name  }}
            </a>
            <div class="relative z-20 mt-auto">
                <blockquote class="space-y-2">
                    <p class="text-lg">
                        &ldquo;{{ quote['message'] }}&rdquo;
                    </p>
                    <footer class="text-sm">
                        {{ quote['author'] }}
                    </footer>
                </blockquote>
            </div>
        </div>
        <div class="lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 mx-auto w-full max-w-[350px]">
                <a :href="route('home')" class="flex items-center justify-center lg:hidden">
                    <ApplicationLogo class="text-black w-12 h-12" />
                </a>
                <slot />
            </div>
        </div>
    </div>
</template>