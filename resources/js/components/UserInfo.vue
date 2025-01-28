<script setup lang="ts">
import { computed } from 'vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { useInitials } from '@/composables/useInitials'
import type { User } from '@/types'

interface Props {
    user: User
}

const { user } = defineProps<Props>()
const getInitials = useInitials()

// Compute whether we should show the avatar image
const showAvatar = computed(() => user.avatar && user.avatar !== '')
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="showAvatar" :src="user.avatar" :alt="user.name" />
        <AvatarFallback class="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>
    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold">{{ user.name }}</span>
        <span class="truncate text-xs">{{ user.email }}</span>
    </div>
</template>
