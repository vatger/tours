<!-- AuthBase.vue -->
<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSplitLayout.vue';
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
