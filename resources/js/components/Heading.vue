<script setup lang="ts">
import { Separator } from '@/components/ui/separator';

interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-0.5">
        <h2 class="text-lg font-bold tracking-tight sm:text-xl md:text-2xl">{{ title }}</h2>
        <p v-if="description" class="text-sm text-muted-foreground md:text-base">
            {{ description }}
        </p>
    </div>
    <Separator class="my-6" />
</template>
