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
        <h2 class="text-base font-semibold tracking-tight sm:text-lg lg:text-xl">{{ title }}</h2>
        <p v-if="description" class="text-xs text-muted-foreground md:text-sm">
            {{ description }}
        </p>
    </div>
    <Separator class="my-6" />
</template>
