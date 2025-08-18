<script setup lang="ts">
import { ref, computed, nextTick, watch, ComputedRef } from 'vue';
import { Head, Form, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { PinInputInput, PinInputRoot } from 'reka-ui';

import { Dialog, DialogContent, DialogHeader, DialogDescription, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Check, Copy, Eye, EyeOff, Loader2, ScanLine, LockKeyhole } from 'lucide-vue-next';

interface QrCodeResponse {
    svg: string;
    url: string;
}

interface SecretKeyResponse {
    secretKey: string;
}

const props = withDefaults(
    defineProps<{
        confirmed?: boolean;
        recoveryCodes?: string[];
    }>(),
    {
        confirmed: false,
        recoveryCodes: () => [],
    },
);

const breadcrumbs = [
    {
        title: 'Two-Factor Authentication',
        href: '/settings/two-factor',
    },
];

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-CSRF-TOKEN': csrfToken,
    'X-Requested-With': 'XMLHttpRequest',
};

const confirmed = ref(props.confirmed);
const qrCodeSvg = ref('');
const secretKey = ref('');
const recoveryCodesList = ref(props.recoveryCodes);
const copied = ref(false);
const error = ref('');
const verifyStep = ref(false);
const showingRecoveryCodes = ref(false);
const showModal = ref(false);

watch([showModal, verifyStep, qrCodeSvg], ([newShowModal, newVerifyStep, newQrCodeSvg]) => {
    if (newShowModal && !newVerifyStep && !newQrCodeSvg) {
        enable();
    }
});

const enable = async () => {
    try {
        const enableResponse = await fetch(route('two-factor.enable'), {
            method: 'POST',
            headers,
        });

        if (enableResponse.ok) {
            const qrResponse = await fetch(route('two-factor.qr-code'), {
                method: 'GET',
                headers,
            });

            const secretResponse = await fetch(route('two-factor.secret-key'), {
                method: 'GET',
                headers,
            });

            if (qrResponse.ok && secretResponse.ok) {
                const qrData: QrCodeResponse = await qrResponse.json();
                const secretData: SecretKeyResponse = await secretResponse.json();

                qrCodeSvg.value = btoa(qrData.svg);
                secretKey.value = secretData.secretKey;
            }
        } else if (enableResponse.status === 423) {
            window.location.reload();
        } else {
            console.error('Error enabling 2FA:', enableResponse.status, enableResponse.statusText);
            const errorText = await enableResponse.text();
            console.error('Response body:', errorText);
        }
    } catch (error) {
        console.error('Error enabling 2FA:', error);
    }
};

const handleRegenerateRecoveryCodes = (page: any) => {
    if (page.props.recoveryCodes) {
        recoveryCodesList.value = page.props.recoveryCodes as string[];
    }
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    copied.value = true;
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

                <div v-if="!confirmed" class="flex flex-col items-start justify-start space-y-5">
                    <Badge variant="outline" class="border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-50"> Disabled </Badge>

                    <p class="-translate-y-1 text-stone-500 dark:text-stone-400">
                        When you enable 2FA, you'll be prompted for a secure code during login, which can be retrieved from your phone's TOTP
                        supported app.
                    </p>

                    <Dialog :open="showModal" @update:open="showModal = $event">
                        <DialogTrigger as-child>
                            <Button @click="showModal = true">Enable</Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader class="flex items-center justify-center">
                                <div
                                    class="mb-3 w-auto rounded-full border border-stone-100 bg-white p-0.5 shadow-sm dark:border-stone-600 dark:bg-stone-800"
                                >
                                    <div
                                        class="relative overflow-hidden rounded-full border border-stone-200 bg-stone-100 p-2.5 dark:border-stone-600 dark:bg-stone-200"
                                    >
                                        <div
                                            class="absolute inset-0 flex h-full w-full items-stretch justify-around divide-x divide-stone-200 opacity-50 dark:divide-stone-300 [&>div]:flex-1"
                                        >
                                            <div v-for="i in 5" :key="i"></div>
                                        </div>
                                        <div
                                            class="absolute inset-0 flex h-full w-full flex-col items-stretch justify-around divide-y divide-stone-200 opacity-50 dark:divide-stone-300 [&>div]:flex-1"
                                        >
                                            <div v-for="i in 5" :key="i"></div>
                                        </div>
                                        <ScanLine class="relative z-20 size-6 dark:text-black" />
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
                                        <div
                                            class="relative mx-auto aspect-square w-64 overflow-hidden rounded-lg border border-stone-200 dark:border-stone-700"
                                        >
                                            <div
                                                v-if="!qrCodeSvg"
                                                class="absolute inset-0 z-10 flex aspect-square h-auto w-full animate-pulse items-center justify-center bg-white dark:bg-stone-700"
                                            >
                                                <Loader2 class="size-6 animate-spin" />
                                            </div>
                                            <div v-else class="relative z-10 overflow-hidden border p-5">
                                                <img
                                                    :src="'data:image/svg+xml;base64,' + qrCodeSvg"
                                                    alt="QR Code"
                                                    class="aspect-square h-full w-full"
                                                />
                                                <div v-if="qrCodeSvg" class="animate-scanning-line absolute inset-0 h-full w-full">
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
                                        <div class="absolute inset-0 top-1/2 h-px w-full bg-stone-200 dark:bg-stone-600"></div>
                                        <span class="relative bg-white px-2 py-1 dark:bg-stone-800"> or, enter the code manually </span>
                                    </div>

                                    <div class="flex w-full items-center justify-center space-x-2">
                                        <div class="flex w-full items-stretch overflow-hidden rounded-xl border dark:border-stone-700">
                                            <div
                                                v-if="!secretKey"
                                                class="flex h-full w-full items-center justify-center bg-stone-100 p-3 dark:bg-stone-700"
                                            >
                                                <Loader2 class="size-4 animate-spin" />
                                            </div>
                                            <template v-else>
                                                <input
                                                    type="text"
                                                    readonly
                                                    :value="secretKey"
                                                    class="h-full w-full bg-white p-3 text-black dark:bg-stone-800 dark:text-stone-100"
                                                />
                                                <button
                                                    @click="copyToClipboard(secretKey)"
                                                    class="relative block h-auto border-l border-stone-200 px-3 hover:bg-stone-100 dark:border-stone-600 dark:hover:bg-stone-600"
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
                                        ref="formRef"
                                        :action="route('two-factor.confirm')"
                                        method="post"
                                        #default="{ errors, processing }"
                                        @success="
                                            () => {
                                                confirmed = true;
                                                verifyStep = false;
                                                showModal = false;
                                                showingRecoveryCodes = true;
                                                pinValue = [];
                                                error = '';
                                            }
                                        "
                                        @error="
                                            () => {
                                                pinValue = [];
                                            }
                                        "
                                    >
                                        <div ref="pinInputContainerRef" class="relative w-full space-y-3">
                                            <input type="hidden" name="code" :value="code" />

                                            <div class="flex w-full flex-col items-center justify-center space-y-3 py-2">
                                                <PinInputRoot
                                                    id="otp"
                                                    type="number"
                                                    v-model="pinValue"
                                                    placeholder="○"
                                                    class="mt-1 flex items-center gap-2"
                                                >
                                                    <PinInputInput
                                                        v-for="(id, index) in 6"
                                                        :key="id"
                                                        :index="index"
                                                        :disabled="processing"
                                                        class="h-10 w-10 rounded-lg border border-input bg-background text-center text-foreground shadow-sm outline-none placeholder:text-muted-foreground focus:shadow-[0_0_0_2px] focus:shadow-ring disabled:cursor-not-allowed disabled:opacity-50"
                                                    />
                                                </PinInputRoot>
                                                <div v-if="errors.confirmTwoFactorAuthentication?.code" class="mt-2 text-sm text-red-600">
                                                    {{ errors.confirmTwoFactorAuthentication.code }}
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

                <div v-if="confirmed" class="flex flex-col items-start justify-start space-y-5">
                    <Badge variant="outline" class="border-green-200 bg-green-50 text-green-700 hover:bg-green-50"> Enabled </Badge>
                    <p class="text-stone-500 dark:text-stone-400">
                        With two factor authentication enabled, you'll be prompted for a secure, random token during login, which you can retrieve
                        from your TOTP Authenticator app.
                    </p>

                    <div>
                        <div class="flex items-start rounded-t-xl border border-stone-200 bg-stone-50 p-4 dark:border-stone-700 dark:bg-stone-800">
                            <LockKeyhole class="mr-2 size-5 text-stone-500" />
                            <div class="space-y-1">
                                <h3 class="font-medium">2FA Recovery Codes</h3>
                                <p class="text-sm text-stone-500 dark:text-stone-400">
                                    Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-b-xl border border-t-0 border-stone-200 bg-stone-100 text-sm dark:border-stone-700 dark:bg-stone-800">
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
                                    :async="false"
                                    #default="{ processing }"
                                    @success="handleRegenerateRecoveryCodes"
                                >
                                    <Button size="sm" variant="outline" class="text-stone-600" type="submit" :disabled="processing" @click.stop>
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
                                <div class="grid max-w-xl gap-1 bg-stone-200 px-4 py-4 font-mono text-sm dark:bg-stone-900 dark:text-stone-100">
                                    <div v-for="(code, index) in recoveryCodesList" :key="index">{{ code }}</div>
                                </div>
                                <p class="px-4 py-3 text-xs text-stone-500 select-none dark:text-stone-400">
                                    You have {{ recoveryCodesList.length }} recovery codes left. Each can be used once to access your account and will
                                    be removed after use. If you need more, click <span class="font-bold">Regenerate Codes</span> above.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="relative inline">
                        <Form
                            :action="route('two-factor.disable')"
                            method="delete"
                            :async="false"
                            #default="{ processing }"
                            @success="() => {
                                confirmed = false;
                                showingRecoveryCodes = false;
                                recoveryCodesList = [];
                                qrCodeSvg = '';
                                secretKey = '';
                            }"
                        >
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
