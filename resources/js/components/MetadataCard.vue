<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Label } from '@/components/ui/label'
import { ChevronDown } from 'lucide-vue-next'
import { ref } from 'vue'
import type { PropType } from 'vue'
const props = defineProps({
    createdAt: {
        type: [String, null] as PropType<string | null>,
        default: null
    },
    updatedAt: {
        type: [String, null] as PropType<string | null>,
        default: null
    },
    deletedAt: {
        type: [String, null] as PropType<string | null>,
        default: null
    },
    title: {
        type: String,
        default: 'Metadata'
    }
})
const isMetadataOpen = ref(false)
</script>

<template>
    <!-- <div class="border rounded-md"> -->
    <Card>
        <Collapsible v-model:open="isMetadataOpen" class="w-full">
            <CardHeader class="p-0 border-b h-14">
                <CollapsibleTrigger as-child>
                    <div
                        class="flex items-center justify-between h-full px-4 py-2 cursor-pointer hover:bg-accent sm:px-6">
                        <div class="flex items-center gap-2">
                            <CardTitle class="text-sm">{{ title }}</CardTitle>
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <ChevronDown class="w-5 h-5 transition-transform duration-200 text-muted-foreground"
                                        :class="{ 'transform rotate-180': isMetadataOpen }" />
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{{ isMetadataOpen ? $t('Close') : $t('Show') }}</p>
                                </TooltipContent>
                            </Tooltip>
                        </div>
                    </div>
                </CollapsibleTrigger>
            </CardHeader>
            <CollapsibleContent>

                <CardContent>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-muted-foreground">Criado em</h3>
                            <p class="text-sm">{{ createdAt || $t('N/A') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-muted-foreground">Atualizado em</h3>
                            <p class="text-sm">{{ updatedAt || $t('N/A') }}</p>
                        </div>
                        <div v-if="deletedAt">
                            <h3 class="text-sm font-medium text-muted-foreground">Excluído em</h3>
                            <p class="text-sm text-red-900">{{ deletedAt || $t('N/A') }}</p>
                        </div>
                    </div>
                </CardContent>


            </CollapsibleContent>
        </Collapsible>
    </Card>
    <!-- </div> -->
</template>
