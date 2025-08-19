<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PinInput, PinInputGroup, PinInputSlot } from '@/components/ui/pin-input';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { computed, ComputedRef, nextTick, ref } from 'vue';

import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Check, Copy, Eye, EyeOff, Loader2, LockKeyhole, ScanLine } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        confirmed?: boolean;
        recoveryCodes?: string[];
        qrCodeSvg?: string | null;
        secretKey?: string | null;
    }>(),
    {
        confirmed: false,
        recoveryCodes: () => [],
        qrCodeSvg: null,
        secretKey: null,
    },
);

const breadcrumbs = [
    {
        title: 'Two-Factor Authentication',
        href: '/settings/two-factor',
    },
];

const copied = ref(false);
const error = ref('');
const verifyStep = ref(false);
const showingRecoveryCodes = ref(false);
const showModal = ref(false);

const showTwoFactorEnableModal = (): void => {
    showModal.value = true;
};

const handleConfirmSuccess = (): void => {
    verifyStep.value = false;
    showModal.value = false;
    showingRecoveryCodes.value = true;
    pinValue.value = [];
    error.value = '';
};

const handleConfirmError = (errors: any): void => {
    error.value = errors.message || 'Invalid verification code';
    pinValue.value = [];
};

const copyToClipboard = (text: string): void => {
    navigator.clipboard.writeText(text).then(() => (copied.value = true));
    setTimeout(() => (copied.value = false), 1500);
};

const pinValue = ref<number[]>([]);
const pinInputContainerRef = ref<HTMLElement | null>(null);
const code: ComputedRef<string> = computed(() => pinValue.value.join(''));

const toggleRecoveryCodes = () => {
    showingRecoveryCodes.value = !showingRecoveryCodes.value;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Two-Factor Authentication" description="Manage your two-factor authentication settings" />
                <div v-if="!props.confirmed" class="flex flex-col items-start justify-start space-y-5">
                    <Badge variant="destructive"> Disabled </Badge>
                    <p class="-translate-y-1 text-muted-foreground">
                        When you enable 2FA, you'll be prompted for a secure code during login, which can be retrieved from your phone's TOTP
                        supported app.
                    </p>
                    <Dialog :open="showModal" @update:open="showModal = $event">
                        <DialogTrigger as-child>
                            <Form
                                :async="false"
                                :action="route('two-factor.enable')"
                                method="post"
                                @success="showTwoFactorEnableModal"
                                #default="{ processing }"
                            >
                                <Button type="submit" :disabled="processing">
                                    {{ processing ? 'Enabling...' : 'Enable' }}
                                </Button>
                            </Form>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader class="flex items-center justify-center">
                                <div class="mb-3 w-auto rounded-full border border-border bg-card p-0.5 shadow-sm">
                                    <div class="relative overflow-hidden rounded-full border border-border bg-muted p-2.5">
                                        <div
                                            class="absolute inset-0 flex size-full items-stretch justify-around divide-x divide-border opacity-50 [&>div]:flex-1"
                                        >
                                            <div v-for="i in 5" :key="i"></div>
                                        </div>
                                        <div
                                            class="absolute inset-0 flex size-full flex-col items-stretch justify-around divide-y divide-border opacity-50 [&>div]:flex-1"
                                        >
                                            <div v-for="i in 5" :key="i"></div>
                                        </div>
                                        <ScanLine class="relative z-20 size-6 text-foreground" />
                                    </div>
                                </div>
                                <DialogTitle>
                                    {{ !verifyStep ? 'Turn on 2-step Verification' : 'Verify Authentication Code' }}
                                </DialogTitle>
                                <DialogDescription class="text-center">
                                    {{
                                        !verifyStep
                                            ? 'Open your authenticator app and choose Scan QR code'
                                            : 'Enter the 6-digit code from your authenticator app'
                                    }}
                                </DialogDescription>
                            </DialogHeader>

                            <div class="relative flex w-auto flex-col items-center justify-center space-y-5">
                                <template v-if="!verifyStep">
                                    <div class="relative mx-auto flex max-w-md items-center overflow-hidden">
                                        <div class="relative mx-auto aspect-square w-64 overflow-hidden rounded-lg border border-border">
                                            <div
                                                v-if="!props.qrCodeSvg"
                                                class="absolute inset-0 z-10 flex aspect-square h-auto w-full animate-pulse items-center justify-center bg-background"
                                            >
                                                <Loader2 class="size-6 animate-spin" />
                                            </div>
                                            <div v-else class="relative z-10 overflow-hidden border p-5">
                                                <div v-html="props.qrCodeSvg" class="flex aspect-square size-full items-center justify-center"></div>
                                                <div v-if="props.qrCodeSvg" class="animate-scanning-line absolute inset-0 h-full w-full">
                                                    <div
                                                        class="absolute inset-x-0 h-0.5 bg-blue-500 opacity-60 transition-all duration-300 ease-in-out"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex w-full items-center space-x-5">
                                        <Button
                                            class="w-full"
                                            @click="
                                                () => {
                                                    verifyStep = true;
                                                    nextTick(() => {
                                                        if (pinInputContainerRef) {
                                                            const firstInput = pinInputContainerRef.querySelector('input');
                                                            if (firstInput) firstInput.focus();
                                                        }
                                                    });
                                                }
                                            "
                                        >
                                            Continue
                                        </Button>
                                    </div>

                                    <div class="relative flex w-full items-center justify-center">
                                        <div class="absolute inset-0 top-1/2 h-px w-full bg-border"></div>
                                        <span class="relative bg-card px-2 py-1"> or, enter the code manually </span>
                                    </div>

                                    <div class="flex w-full items-center justify-center space-x-2">
                                        <div class="flex w-full items-stretch overflow-hidden rounded-xl border border-border">
                                            <div v-if="!props.secretKey" class="flex h-full w-full items-center justify-center bg-muted p-3">
                                                <Loader2 class="size-4 animate-spin" />
                                            </div>
                                            <template v-else>
                                                <input
                                                    type="text"
                                                    readonly
                                                    :value="props.secretKey"
                                                    class="h-full w-full bg-background p-3 text-foreground"
                                                />
                                                <button
                                                    @click="copyToClipboard(props.secretKey || '')"
                                                    class="relative block h-auto border-l border-border px-3 hover:bg-muted"
                                                >
                                                    <Check v-if="copied" class="w-4 text-green-500" />
                                                    <Copy v-else class="w-4" />
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <Form
                                        :action="route('two-factor.confirm')"
                                        method="post"
                                        :async="false"
                                        @success="handleConfirmSuccess"
                                        @error="handleConfirmError"
                                        #default="{ processing }"
                                    >
                                        <input type="hidden" name="code" :value="code" />
                                        <div ref="pinInputContainerRef" class="relative w-full space-y-3">
                                            <div class="flex w-full flex-col items-center justify-center space-y-3 py-2">
                                                <PinInput id="otp" v-model="pinValue" class="mt-1" type="number" otp>
                                                    <PinInputGroup class="gap-2">
                                                        <PinInputSlot
                                                            v-for="(id, index) in 6"
                                                            :key="id"
                                                            :index="index"
                                                            :disabled="processing"
                                                            class="h-10 w-10 rounded-lg"
                                                        />
                                                    </PinInputGroup>
                                                </PinInput>
                                                <div v-if="error" class="mt-2 text-sm text-red-600">
                                                    {{ error }}
                                                </div>
                                            </div>

                                            <div class="flex w-full items-center space-x-5">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    class="w-auto flex-1"
                                                    @click="verifyStep = false"
                                                    :disabled="processing"
                                                >
                                                    Back
                                                </Button>
                                                <Button type="submit" class="w-auto flex-1" :disabled="processing || pinValue.length < 6">
                                                    {{ processing ? 'Confirming...' : 'Confirm' }}
                                                </Button>
                                            </div>
                                        </div>
                                    </Form>
                                </template>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>

                <div v-if="props.confirmed" class="flex flex-col items-start justify-start space-y-5">
                    <Badge variant="default"> Enabled </Badge>
                    <p class="text-muted-foreground">
                        With two factor authentication enabled, you'll be prompted for a secure, random token during login, which you can retrieve
                        from your TOTP Authenticator app.
                    </p>

                    <div>
                        <div class="flex items-start rounded-t-xl border border-secondary p-4">
                            <LockKeyhole class="mr-2 size-5 text-muted-foreground" />
                            <div class="space-y-1">
                                <h3 class="font-medium">2FA Recovery Codes</h3>
                                <p class="text-sm text-muted-foreground">
                                    Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-b-xl border border-t-0 border-stone-200 bg-secondary text-sm dark:border-stone-700">
                            <div
                                @click="toggleRecoveryCodes"
                                class="group flex h-10 cursor-pointer items-center justify-between px-5 text-xs select-none"
                            >
                                <div :class="`relative ${!showingRecoveryCodes ? 'opacity-40 hover:opacity-60' : 'opacity-60'}`">
                                    <span v-if="!showingRecoveryCodes" class="flex items-center space-x-1">
                                        <Eye class="size-4" /> <span>View My Recovery Codes</span>
                                    </span>
                                    <span v-else class="flex items-center space-x-1">
                                        <EyeOff class="size-4" /> <span>Hide Recovery Codes</span>
                                    </span>
                                </div>

                                <Form
                                    v-if="showingRecoveryCodes"
                                    :action="route('two-factor.recovery-codes')"
                                    method="post"
                                    #default="{ processing }"
                                    :options="{
                                        preserveScroll: true,
                                    }"
                                >
                                    <Button size="sm" variant="ghost" class="text-underline" type="submit" :disabled="processing" @click.stop>
                                        {{ processing ? 'Regenerating...' : 'Regenerate Codes' }}
                                    </Button>
                                </Form>
                            </div>

                            <div
                                class="relative overflow-hidden transition-all duration-300"
                                :style="{
                                    height: showingRecoveryCodes ? 'auto' : '0',
                                    opacity: showingRecoveryCodes ? 1 : 0,
                                }"
                            >
                                <div class="grid max-w-xl gap-1 bg-secondary p-4 font-mono text-sm">
                                    <div v-for="(code, index) in props.recoveryCodes" :key="index">{{ code }}</div>
                                </div>
                                <p class="px-4 py-3 text-xs text-muted-foreground select-none">
                                    You have {{ props.recoveryCodes.length }} recovery codes left. Each can be used once to access your account and
                                    will be removed after use. If you need more, click <span class="font-bold">Regenerate Codes</span> above.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="relative inline">
                        <Form :action="route('two-factor.disable')" method="delete" async="true" #default="{ processing }">
                            <Button variant="destructive" type="submit" :disabled="processing">
                                {{ processing ? 'Disabling...' : 'Disable 2FA' }}
                            </Button>
                        </Form>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
@keyframes scan {
    0% {
        top: 0;
    }
    50% {
        top: 100%;
    }
    100% {
        top: 0;
    }
}

.animate-scanning-line div {
    animation: scan 3s ease-in-out infinite;
}
</style>
