<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
// import "@/app.css";
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="md:hidden">
        <img
            alt="Authentication"
            width="1280"
            height="1214" class="block" :image="{
                dark: '/examples/authentication-dark.png',
                light: '/examples/authentication-light.png',
            }"
            />
        </div>

    <div class="container relative hidden h-screen flex-col items-center justify-center md:grid lg:max-w-none lg:grid-cols-2 lg:px-0">
        <slot />
    </div>
</template>
