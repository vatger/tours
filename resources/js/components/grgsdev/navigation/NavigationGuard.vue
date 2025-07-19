<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { DialogExit } from '@/components/grgsdev/dialog-exit';

const props = defineProps<{
    formIsDirty: boolean
    confirmationText?: string
    isNavigatingAway?: boolean
}>()

const emit = defineEmits<{
    (e: 'navigation-attempt', url: string): void
    (e: 'confirm'): void
    (e: 'cancel'): void
}>()

const showDialog = ref(false)

const beforeUnloadHandler = (e: BeforeUnloadEvent) => {
    if (props.formIsDirty) {
        e.preventDefault()
        e.returnValue = props.confirmationText || 'You have unsaved changes. Are you sure you want to leave?'
    }
}

const setupInertiaNavigation = () => {
    return router.on('before', (event) => {
        const isFormSubmission = ['post', 'put', 'patch'].includes(
            event.detail.visit.method.toLowerCase()
        )

        const shouldBlock =
            props.formIsDirty &&
            !props.isNavigatingAway &&
            !isFormSubmission &&
            event.detail.visit.url.href !== window.location.href

        if (shouldBlock) {
            event.preventDefault()
            showDialog.value = true
            emit('navigation-attempt', event.detail.visit.url.href)
        }
    })
}

onMounted(() => {
    window.addEventListener('beforeunload', beforeUnloadHandler)
    const unsubscribe = setupInertiaNavigation()

    onUnmounted(() => {
        window.removeEventListener('beforeunload', beforeUnloadHandler)
        unsubscribe()
    })
})

const handleConfirm = () => {
    showDialog.value = false
    emit('confirm')
    router.cancel() // Garante cancelamento de visitas anteriores [5][9]
}

const handleCancel = () => {
    showDialog.value = false
    emit('cancel')
}

</script>

<template>
    <DialogExit v-model="showDialog" :title="confirmationText || 'Alterações não salvas'" @confirm="handleConfirm"
        @cancel="handleCancel" />
</template>
