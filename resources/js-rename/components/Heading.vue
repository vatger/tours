<script setup lang="ts">
import { Separator } from '@/components/ui/separator'

interface Props {
  title: string
  description?: string
}

defineProps<Props>()
</script>

<template>
  <div class="space-y-0.5">
    <h2 class="text-lg sm:text-xl md:text-2xl font-bold tracking-tight">{{ title }}</h2>
    <p v-if="description" class="text-sm md:text-base text-muted-foreground">
      {{ description }}
    </p>
  </div>
  <Separator class="my-6" />
</template>
