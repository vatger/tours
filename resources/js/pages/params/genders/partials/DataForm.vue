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
import { LogActivity } from '@/components/grgsdev/log-activity';
import { useAuth } from '@/composables/useAuth'
import { trans } from 'laravel-vue-i18n';
const modelName = 'Gender';
const { hasPermission } = useAuth()
const props = defineProps<{
    gender: any | null, // Allow gender to be null for create action;
    action: 'create' | 'edit'
    open: boolean,
    widthClass?: string
}>()
const activities = computed(() => props.gender?.activities || []);
const emit = defineEmits(['update:open', 'success'])
// Form initialization
const form = useForm({
    name: '',
    icon: null as File | null,
    remove_icon: false as boolean,
})
// Avatar handling
const iconPreview = ref<string | null>(null)
const iconFile = ref<HTMLInputElement | null>(null)
watch(
    () => [props.open, props.gender],
    ([open]) => {
        if (open) {
            resetForm()
        }
    },
    { immediate: true }
)
const resetForm = () => {
    form.reset()
    form.remove_icon = false

    if (props.action === 'edit' && props.gender) {
        form.name = props.gender?.name || ''
        iconPreview.value = props.gender?.icon_url || null
        form.errors = {}
    } else {
        form.name = ''
        form.icon = null
        form.remove_icon = false
        iconPreview.value = null
        form.errors = {}
    }
}
const handleAvatarChange = (event: Event) => {
    const input = event.target as HTMLInputElement
    if (input.files && input.files[0]) {
        form.icon = input.files[0]
        form.remove_icon = false
        const reader = new FileReader()
        reader.onload = (e) => {
            iconPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(input.files[0])
    }
}
const removeAvatar = () => {
    iconPreview.value = null
    form.icon = null
    form.remove_icon = true
    if (iconFile.value) {
        iconFile.value.value = ''
    }
}
const submit = () => {
    const url = props.action === 'create'
        ? route('genders.store')
        : route('genders.update', props.gender?.id)

    const method = props.action === 'create' ? 'post' : 'put'

    const formData = new FormData()
    formData.append('name', form.name)
    if (form.icon) {
        formData.append('icon', form.icon)
    }
    if (form.remove_icon) {
        formData.append('remove_icon', '1')
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
                    {{ action === 'create' ? trans('Create') : trans('Edit') }} {{ $t(modelName) }}
                </DialogTitle>
            </DialogHeader>
            <Card>
                <CardContent class="block overflow-y-auto max-h-[calc(100vh-280px)]">
                    <div class="space-y-4 py-4">
                        <div class="flex flex-col items-center space-y-2">
                            <Avatar class="h-24 w-24">
                                <AvatarImage :src="iconPreview || ''" />
                                <AvatarFallback>{{ form.name?.charAt(0) || 'U' }}</AvatarFallback>
                            </Avatar>
                            <div class="flex flex-col items-center gap-2">
                                <Label for="icon" class="cursor-pointer text-center">
                                    {{ $t('Change') }}
                                    <input id="icon" ref="iconFile" type="file" accept="image/*"
                                        @change="handleAvatarChange" class="hidden" />
                                </Label>
                                <Button v-if="iconPreview || (action === 'edit' && gender?.icon_url && !iconPreview)"
                                    variant="ghost" size="sm" class="text-destructive text-sm h-8"
                                    @click="removeAvatar">
                                    {{ $t('Remove') }}
                                </Button>
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">{{ $t('Name') }}</Label>
                                <Input id="name" v-model="form.name" />
                                <p class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Display timestamps -->
                    <MetadataCard v-if="props.action === 'edit'" :created-at="props.gender?.created_at?.short_with_time"
                        :updated-at="props.gender?.updated_at?.short_with_time"
                        :deleted-at="props.gender?.deleted_at?.short_with_time" :title="$t('Metadata')" />
                    <LogActivity v-if="props.action === 'edit'" :activities="activities" :initial-expanded="false" />
                </CardContent>
                <CardFooter class="flex justify-end gap-2 pt-4">
                    <div class="gap-6 flex">
                        <Button variant="outline" @click="emit('update:open', false)">
                            {{ $t('Cancel') }}
                        </Button>
                        <Button v-if="action === 'create' && hasPermission('genders.create')" type="submit"
                            @click="submit" :disabled="form.processing">
                            {{ $t('Create') }}
                        </Button>
                        <Button v-if="action === 'edit' && hasPermission('genders.edit')" type="submit" @click="submit"
                            :disabled="form.processing">
                            {{ $t('Update') }}
                        </Button>
                    </div>
                </CardFooter>
            </Card>
        </DialogContent>
    </Dialog>
</template>
