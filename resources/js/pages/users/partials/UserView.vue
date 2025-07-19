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
import { LogAction, LogActivity, LogApproveStatus } from '@/components/grgsdev/log-activity';

const props = withDefaults(defineProps<{
    user: any | null
    open: boolean
    widthClass?: string
}>(), {
    widthClass: 'min-w-[70vw] max-w-3xl'
});

// Use computed para lidar com user null
const activities = computed(() => props.user?.activities || []);
const actions = computed(() => props.user?.actions || []);
const approvedMotives = computed(() => props.user?.approvedMotives || []);
defineEmits(['update:open']);
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent :class="['w-full', 'max-w-full', 'sm:max-w-[625px]', widthClass]" @pointer-down-outside.prevent
            @interact-outside.prevent>
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold">
                    {{ $t('User Details') }}
                </DialogTitle>
            </DialogHeader>

            <!-- Renderizar apenas se user existir -->
            <Card v-if="user">
                <CardHeader class="text-lg font-semibold">
                    <Badge v-if="user?.deleted_at?.short" variant="destructive" class="text-xs">
                        {{ $t('Deleted') }}
                    </Badge>
                </CardHeader>

                <CardContent class="block overflow-y-auto max-h-[70vh]">
                    <div class="space-y-4">
                        <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:gap-6">
                            <Avatar class="h-24 w-24 shrink-0">
                                <AvatarImage :src="user.avatar_url" />
                                <AvatarFallback>{{ user.name.charAt(0) }}</AvatarFallback>
                            </Avatar>
                            <div class="flex flex-col items-center sm:items-start gap-2">
                                <h2 class="text-xl font-semibold break-all text-center sm:text-left">{{ user.name }}
                                </h2>
                                <Badge variant="outline" class="break-all">{{ user.email }}</Badge>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">{{ $t('Username') }}</h3>
                                <p>{{ user.username }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">{{ $t('Nickname') }}</h3>
                                <p>{{ user.nickname }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">{{ $t('Locale') }}</h3>
                                <p>{{ user.locale }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">ID</h3>
                                <p>{{ user.id }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Status</h3>
                                <Badge v-if="!user.is_active" variant="destructive" class="text-xs">
                                    {{ user.is_active_text }}
                                </Badge>
                                <Badge v-else variant="default" class="text-xs">
                                    {{ user.is_active_text }}
                                </Badge>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground">Condition</h3>
                                <Badge variant="secondary" class="text-xs">
                                    {{ user.approved_status_text }}
                                </Badge>
                            </div>
                        </div>
                        <MetadataCard :created-at="user?.created_at?.short_with_time"
                            :updated-at="user?.updated_at?.short_with_time"
                            :deleted-at="user?.deleted_at?.short_with_time" :title="$t('Metadata')" />
                        <LogActivity :activities="activities" :initial-expanded="false" />
                        <LogAction :activities="actions" :initial-expanded="false" />
                        <LogApproveStatus :activities="approvedMotives" :initial-expanded="false" />
                    </div>
                </CardContent>

                <CardFooter class="flex flex-col sm:flex-row justify-end">
                    <Button variant="outline" @click="$emit('update:open', false)">Fechar</Button>
                </CardFooter>
            </Card>
        </DialogContent>
    </Dialog>
</template>
