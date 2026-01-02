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
  <div class="inline-flex items-center justify-center rounded bg-gray-800">
    <img
      src="https://vatsim-germany.org/favicon.svg"
      class="h-6 w-6"
      alt="VATSIM Germany"
    />
  </div>
</template>
