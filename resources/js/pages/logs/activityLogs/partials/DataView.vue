<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog'


const props = withDefaults(defineProps<{
    object: any | null
    open: boolean
    widthClass?: string
}>(), {
    widthClass: 'min-w-[70vw] max-w-3xl'
});

defineEmits(['update:open']);
</script>

<template>

    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent :class="['w-full', 'max-w-full', 'sm:max-w-[625px]', widthClass]" @pointer-down-outside.prevent
            @interact-outside.prevent>
            <DialogHeader>
                <DialogTitle>{{ $t('Log Activity Details') }}</DialogTitle>
                <DialogDescription>
                    {{ $t('Viewing log details for the selected activity') }}
                </DialogDescription>
                <DialogDescription>
                    {{ props.object?.causer_name || 'System' }} has {{ props.object?.description }}
                    at
                    {{ props.object?.created_at.short }} the {{ props.object?.subject_type || 'Unknown' }}
                    {{ props.object?.subject_name || 'Unknown' }}.
                </DialogDescription>
            </DialogHeader>
            <div v-if="object && object.properties" class="w-full max-w-[70vw] max-h-[80vh] overflow-y-auto mt-4">
                <table class="w-full text-sm text-left border-collapse table-auto">
                    <thead class="sticky top-0 z-20 bg-white shadow-sm dark:bg-gray-800">
                        <tr>
                            <th class="w-1/2 p-2 font-semibold bg-gray-100 dark:bg-gray-800">
                                {{ $t('Old') }}
                            </th>
                            <th class="w-1/2 p-2 font-semibold bg-gray-100 dark:bg-gray-800">
                                {{ $t('Attributes') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template
                            v-for="key in Object.keys({ ...(object.properties.old || {}), ...(object.properties.attributes || {}) })"
                            :key="key">
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-2 bg-gray-50 dark:bg-gray-900">
                                    <span :class="{
                                        'font-semibold text-blue-600': object.properties.attributes && object.properties.attributes[key] !== (object.properties.old ? object.properties.old[key] : undefined)
                                    }">
                                        {{ $t(key) }}: {{ object.properties.old ?
                                            object.properties.old[key] || '-' : '-' }}
                                    </span>
                                </td>
                                <td class="p-2 bg-gray-50 dark:bg-gray-900">
                                    <span :class="{
                                        'font-semibold text-green-600': object.properties.old && object.properties.attributes && object.properties.attributes[key] !== (object.properties.old ? object.properties.old[key] : undefined)
                                    }">
                                        {{ $t(key) }}: {{ object.properties.attributes ?
                                            object.properties.attributes[key] || '-' : '-' }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div v-if="!Object.keys(object.properties.old || {}).length && !Object.keys(object.properties.attributes || {}).length"
                    class="bg-gray-100 dark:bg-gray-800 p-4 rounded-md overflow-auto max-h-[400px] text-sm text-gray-900 dark:text-gray-100 text-center">
                    {{ $t('No data available') }}
                </div>
            </div>

            <div v-else
                class="bg-gray-100 dark:bg-gray-800 p-4 rounded-md overflow-auto max-h-[400px] text-sm text-gray-900 dark:text-gray-100 text-center">
                {{ $t('No activity data available') }}
            </div>
        </DialogContent>
    </Dialog>


</template>
