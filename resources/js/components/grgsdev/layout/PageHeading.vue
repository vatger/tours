<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
defineProps<{
    title: string | undefined
    description?: string
    backRoute?: string
}>()
const back = (visit: string | null) => {
    if (visit) {
        router.visit(visit);
    } else {
        router.visit('/users');
    }
};
</script>

<template>
    <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold">{{ title }}</h1>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <Button v-if="backRoute" variant="link" @click="back(backRoute)">
            {{ $t('Back') }}
        </Button>
    </div>
</template>
