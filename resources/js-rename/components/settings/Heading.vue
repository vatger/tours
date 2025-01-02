<script setup lang="ts">
import { Separator } from '@/components/ui/separator'

interface Props {
  title: string
  description?: string
}

defineProps<Props>()
</script>

<template>
  <div>
    <header>
      <h3 class="text-lg font-medium">{{ title }}</h3>
      <p v-if="description" class="text-sm text-muted-foreground">
        {{ description }}
      </p>
    </header>
    <Separator class="my-6" />
  </div>
</template>
