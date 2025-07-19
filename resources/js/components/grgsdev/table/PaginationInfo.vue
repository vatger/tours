<template>
    <div class="text-sm text-gray-500 dark:text-gray-400">
        {{ $t('Showing') }} {{ from || 0 }} {{ $t('to') }} {{ to || 0 }} {{ $t('of') }} {{ total }}
        {{ $t('Entries') }}
    </div>
</template>

<script setup lang="ts">
defineProps<{
    from?: number;
    to?: number;
    total: number;
}>();
</script>
