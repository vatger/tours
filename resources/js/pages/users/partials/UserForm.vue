<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

import { watch, ref, computed } from 'vue'
import MetadataCard from '@/components/MetadataCard.vue';

import { LogAction, LogActivity } from '@/components/grgsdev/log-activity';
import { useAuth } from '@/composables/useAuth'

const { hasPermission } = useAuth()

const props = defineProps<{
    user: any | null, // Allow user to be null for create action;
    action: 'create' | 'edit'
    open: boolean,
    widthClass?: string
}>()

const activities = computed(() => props.user?.activities || []);
const actions = computed(() => props.user?.actions || []);

const emit = defineEmits(['update:open', 'success'])

// Form initialization
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    avatar: null as File | null,
    remove_avatar: false as boolean,
    username: '',
    pin_code: '',
    locale: 'pt_BR',
    nickname: ''
})

// Avatar handling
const avatarPreview = ref<string | null>(null)
const avatarFile = ref<HTMLInputElement | null>(null)


watch(
    () => [props.open, props.user],
    ([open]) => {
        if (open) {
            resetForm()
        }
    },
    { immediate: true }
)
const resetForm = () => {
    form.reset()
    form.remove_avatar = false

    if (props.action === 'edit' && props.user) {
        form.name = props.user?.name || ''
        form.email = props.user?.email || ''
        form.username = props.user?.username || ''
        form.pin_code = props.user?.pin_code || ''
        form.locale = props.user?.locale || 'pt_BR'
        form.nickname = props.user?.nickname || ''
        avatarPreview.value = props.user?.avatar_url || null
        form.errors = {}
    } else {
        form.name = ''
        form.email = ''
        form.username = ''
        form.pin_code = ''
        form.locale = 'pt_BR'
        form.nickname = ''
        form.avatar = null
        form.remove_avatar = false
        avatarPreview.value = null
        form.errors = {}
    }

    form.password = ''
    form.password_confirmation = ''
}

const handleAvatarChange = (event: Event) => {
    const input = event.target as HTMLInputElement
    if (input.files && input.files[0]) {
        form.avatar = input.files[0]
        form.remove_avatar = false

        const reader = new FileReader()
        reader.onload = (e) => {
            avatarPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(input.files[0])
    }
}

const removeAvatar = () => {
    avatarPreview.value = null
    form.avatar = null
    form.remove_avatar = true
    if (avatarFile.value) {
        avatarFile.value.value = ''
    }
}

const submit = () => {
    const url = props.action === 'create'
        ? route('users.store')
        : route('users.update', props.user?.id)

    const method = props.action === 'create' ? 'post' : 'put'

    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('email', form.email)
    formData.append('username', form.username)
    formData.append('pin_code', form.pin_code)
    formData.append('locale', form.locale)
    formData.append('nickname', form.nickname)

    // Only include password if it's being changed
    if (form.password) {
        formData.append('password', form.password)
        formData.append('password_confirmation', form.password_confirmation)
    }

    if (form.avatar) {
        formData.append('avatar', form.avatar)
    }

    if (form.remove_avatar) {
        formData.append('remove_avatar', '1')
    }

    if (method === 'put') {
        formData.append('_method', 'PUT')
    }

    form.transform(() => formData).post(url, {
        onSuccess: () => {
            emit('success')
            emit('update:open', false)
            resetForm()
        },
        forceFormData: true
    })
}
</script>

<template>
    <!-- <Dialog :open="open" @update:open="emit('update:open', $event)"> -->
    <Dialog :open="open" @update:open="emit('update:open', $event)">

        <DialogContent :class="['sm:max-w-[625px]', widthClass]" @pointer-down-outside.prevent
            @interact-outside.prevent>
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold">
                    {{ action === 'create' ? 'Create New User' : 'Edit User' }}
                </DialogTitle>
            </DialogHeader>
            <Card>
                <!-- <CardHeader class="text-lg font-semibold">
                    <CardTitle>{{ action === 'create' ? 'Create New User' : 'Edit User' }}</CardTitle>
                </CardHeader> -->
                <CardContent class="block overflow-y-auto max-h-[calc(100vh-280px)]">
                    <div class="space-y-4 py-4">
                        <div class="flex flex-col items-center space-y-2">
                            <Avatar class="h-24 w-24">
                                <AvatarImage :src="avatarPreview || ''" />
                                <AvatarFallback>{{ form.name?.charAt(0) || 'U' }}</AvatarFallback>
                            </Avatar>
                            <div class="flex flex-col items-center gap-2">
                                <Label for="avatar" class="cursor-pointer text-center">
                                    Change Avatar
                                    <input id="avatar" ref="avatarFile" type="file" accept="image/*"
                                        @change="handleAvatarChange" class="hidden" />
                                </Label>
                                <Button
                                    v-if="avatarPreview || (action === 'edit' && user?.avatar_url && !avatarPreview)"
                                    variant="ghost" size="sm" class="text-destructive text-sm h-8"
                                    @click="removeAvatar">
                                    Remove Avatar
                                </Button>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">{{ $t('validation.attributes.name') }}</Label>
                                <Input id="name" v-model="form.name" />
                                <p class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">{{ $t('validation.attributes.email') }}</Label>
                                <Input id="email" type="email" v-model="form.email" />
                                <p class="text-sm text-destructive">{{ form.errors.email }}</p>
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="username">{{ $t('validation.attributes.username') }}</Label>
                                <Input id="username" v-model="form.username" />
                                <p class="text-sm text-destructive">{{ form.errors.username }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="nickname">{{ $t('validation.attributes.nickname') }}</Label>
                                <Input id="nickname" v-model="form.nickname" />
                                <p class="text-sm text-destructive">{{ form.errors.nickname }}</p>
                            </div>

                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="locale">{{ $t('validation.attributes.locale') }}</Label>
                                <Input id="locale" v-model="form.locale" />
                                <p class="text-sm text-destructive">{{ form.errors.locale }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="pin_code">{{ $t('validation.attributes.pin_code') }}</Label>
                                <Input id="pin_code" type="password" v-model="form.pin_code" />
                                <p class="text-sm text-destructive">{{ form.errors.pin_code }}</p>
                            </div>

                        </div>


                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="password">{{ action === 'create' ? 'Password' : 'New Password' }}</Label>
                                <Input id="password" type="password" v-model="form.password" />
                                <p class="text-sm text-destructive">{{ form.errors.password }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input id="password_confirmation" type="password"
                                    v-model="form.password_confirmation" />
                            </div>
                        </div>
                    </div>
                    <!-- Display timestamps -->
                    <MetadataCard v-if="props.action === 'edit'" :created-at="props.user?.created_at?.short_with_time"
                        :updated-at="props.user?.updated_at?.short_with_time"
                        :deleted-at="props.user?.deleted_at?.short_with_time" :title="$t('Metadata')" />
                    <LogActivity v-if="props.action === 'edit'" :activities="activities" :initial-expanded="false" />

                    <LogAction v-if="props.action === 'edit'" :activities="actions" :initial-expanded="false" />


                </CardContent>
                <CardFooter class="flex justify-end gap-2 pt-4">
                    <div class="gap-6 flex">
                        <Button variant="outline" @click="emit('update:open', false)">
                            {{ $t('actions.cancel') }}
                        </Button>
                        <Button v-if="action === 'create' && hasPermission('users.create')" type="submit"
                            @click="submit" :disabled="form.processing">
                            {{ $t('actions.create') }}
                        </Button>

                        <Button v-if="action === 'edit' && hasPermission('users.edit')" type="submit" @click="submit"
                            :disabled="form.processing">
                            {{ $t('actions.update') }}
                        </Button>
                    </div>
                </CardFooter>
            </Card>


        </DialogContent>
    </Dialog>
</template>
