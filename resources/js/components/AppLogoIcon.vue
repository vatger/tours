<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
  inheritAttrs: false,
});

interface Props {
  className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
  <div v-bind="$attrs" :class="['inline-flex items-center justify-start overflow-hidden', className]">
    <img src="/branding/logo/logo_light.svg" class="h-full max-w-none shrink-0 dark:hidden" alt="VATGER Tours" />
    <img src="/branding/logo/logo_dark.svg" class="hidden h-full max-w-none shrink-0 dark:block" alt="VATGER Tours" />
  </div>
</template>
