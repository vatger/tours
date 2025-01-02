<!-- AuthBase.vue -->
<script setup lang="ts">
import { defineProps } from 'vue';
import AuthLayout from '@/@/components/settings/AuthSplitLayout.vue';

interface Props {
    title?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: undefined,
    description: undefined
});
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
