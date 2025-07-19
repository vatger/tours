<script setup lang="ts">
import { computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Card, CardHeader, CardContent, CardFooter } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import MetadataCard from '@/components/MetadataCard.vue';
import { LogActivity } from '@/components/grgsdev/log-activity';

const props = withDefaults(defineProps<{
    gender: any | null
    open: boolean
    widthClass?: string
}>(), {
    widthClass: 'min-w-[70vw] max-w-3xl'
});
// Use computed para lidar com gender null
const activities = computed(() => props.gender?.activities || []);
defineEmits(['update:open']);
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent :class="['w-full', 'max-w-full', 'sm:max-w-[625px]', widthClass]" @pointer-down-outside.prevent
            @interact-outside.prevent>
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold">
                    {{ $t('Gender') }}
                    <span>{{ $t('Details') }}</span>
                </DialogTitle>
            </DialogHeader>
            <!-- Renderizar apenas se gender existir -->
            <Card v-if="gender">
                <CardHeader class="text-lg font-semibold">
                    <Badge v-if="gender?.deleted_at?.short" variant="destructive" class="text-xs">
                        {{ $t('Deleted') }}
                    </Badge>
                </CardHeader>
                <CardContent class="block overflow-y-auto max-h-[70vh]">
                    <div class="space-y-4">
                        <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:gap-6">
                            <Avatar class="h-24 w-24 shrink-0">
                                <AvatarImage :src="gender.icon_url" />
                                <AvatarFallback>{{ gender.name.charAt(0) }}</AvatarFallback>
                            </Avatar>
                            <div class="flex flex-col items-center sm:items-start gap-2">
                                <h2 class="text-xl font-semibold break-all text-center sm:text-left">{{
                                    gender.name_translated }}
                                </h2>

                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">{{ $t('ID') }}</h3>
                                <p>{{ gender.id }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">{{ $t('Status') }}</h3>
                                <Badge v-if="!gender.is_active" variant="destructive" class="text-xs">
                                    {{ gender.is_active_text }}
                                </Badge>
                                <Badge v-else variant="default" class="text-xs">
                                    {{ gender.is_active_text }}
                                </Badge>
                            </div>
                        </div>
                        <MetadataCard :created-at="gender?.created_at?.short_with_time"
                            :updated-at="gender?.updated_at?.short_with_time"
                            :deleted-at="gender?.deleted_at?.short_with_time" :title="$t('Metadata')" />
                        <LogActivity :activities="activities" :initial-expanded="false" />
                    </div>
                </CardContent>
                <CardFooter class="flex flex-col sm:flex-row justify-end">
                    <Button variant="outline" @click="$emit('update:open', false)">{{ $t('Close') }}</Button>
                </CardFooter>
            </Card>
        </DialogContent>
    </Dialog>
</template>
