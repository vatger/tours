<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { PinInput, PinInputGroup, PinInputSlot } from '@/components/ui/pin-input';
import { useClipboard } from '@/composables/useClipboard';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { Check, Copy, Eye, EyeOff, Loader2, LockKeyhole, RefreshCw, ScanLine, ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { computed, ComputedRef, nextTick, reactive, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        requiresConfirmation?: boolean;
        twoFactorEnabled?: boolean;
    }>(),
    {
        requiresConfirmation: false,
        twoFactorEnabled: false,
    },
);

const breadcrumbs = [
    {
        title: 'Two-Factor Authentication',
        href: '/settings/two-factor',
    },
];

const { copied, copyToClipboard } = useClipboard();

const fetchQRCode = async (): Promise<{ svg: string }> => {
    const response = await fetch(route('two-factor.qr-code'));
    return await response.json();
};

const fetchManualSetupKey = async (): Promise<{ secretKey: string }> => {
    const response = await fetch(route('two-factor.secret-key'));
    return await response.json();
};

const fetchRecoveryCodes = async (): Promise<string[]> => {
    const response = await fetch(route('two-factor.recovery-codes'));
    return await response.json();
};

const setupData = reactive({
    qrCodeSvg: null as string | null,
    manualSetupKey: null as string | null,
});

const modalState = reactive({
    isOpen: false,
    isInVerificationStep: false,
});

const recoveryCodes = reactive({
    list: [] as string[],
    isVisible: false,
});

const enableTwoFactorAuthenticationSuccess = (): void => {
    Promise.all([
        fetchQRCode().then((data) => (setupData.qrCodeSvg = data.svg)),
        fetchManualSetupKey().then((data) => (setupData.manualSetupKey = data.secretKey)),
        fetchRecoveryCodes().then((codes) => (recoveryCodes.list = codes)),
    ]).then(() => {
        modalState.isOpen = true;
    });
};

const code = ref<number[]>([]);
const pinInputContainerRef = ref<HTMLElement | null>(null);
const verificationCode: ComputedRef<string> = computed(() => code.value.join(''));

const toggleRecoveryCodesVisibility = () => {
    if (!recoveryCodes.isVisible && recoveryCodes.list.length === 0) {
        fetchRecoveryCodes().then((codes) => (recoveryCodes.list = codes));
    }
    recoveryCodes.isVisible = !recoveryCodes.isVisible;
};

const disableTwoFactorAuthenticationSuccess = (): void => {
    modalState.isInVerificationStep = false;
    modalState.isOpen = false;
    setupData.qrCodeSvg = null;
    setupData.manualSetupKey = null;
    recoveryCodes.list = [];
    recoveryCodes.isVisible = false;
    code.value = [];
};

const modalConfig = computed(() => {
    if (props.twoFactorEnabled) {
        return {
            title: 'You have enabled two factor authentication.',
            description: 'Two factor authentication is now enabled, scan the QR code or enter the setup key',
            buttonText: 'Close',
        };
    }

    if (modalState.isInVerificationStep) {
        return {
            title: 'Verify Authentication Code',
            description: 'Enter the 6-digit code from your authenticator app',
            buttonText: 'Continue',
        };
    }

    return {
        title: 'Turn on 2-step Verification',
        description: 'To finish enabling two factor authentication, scan the QR code or enter the setup key',
        buttonText: 'Continue',
    };
});

const handleSetupAction = (): void => {
    if (props.twoFactorEnabled) {
        modalState.isOpen = false;
        return;
    }

    if (props.requiresConfirmation) {
        modalState.isInVerificationStep = true;
        nextTick(() => {
            if (pinInputContainerRef.value) {
                const firstInput = pinInputContainerRef.value.querySelector('input');
                if (firstInput) firstInput.focus();
            }
        });
        return;
    }

    modalState.isOpen = false;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Two-Factor Authentication" description="Manage your two-factor authentication settings" />
                <div v-if="!twoFactorEnabled" class="flex flex-col items-start justify-start space-y-4">
                    <Badge variant="destructive"> Disabled </Badge>
                    <p class="text-muted-foreground">
                        When you enable 2FA, you'll be prompted for a secure code during login, which can be retrieved from your phone's TOTP
                        supported app.
                    </p>
                    <Dialog v-model:open="modalState.isOpen">
                        <DialogTrigger as-child>
                            <template v-if="setupData.qrCodeSvg && setupData.manualSetupKey">
                                <Button @click="modalState.isOpen = true"> <ShieldCheck />Enable </Button>
                            </template>
                            <template v-else>
                                <Form
                                    :action="route('two-factor.enable')"
                                    method="post"
                                    @success="enableTwoFactorAuthenticationSuccess"
                                    #default="{ processing }"
                                >
                                    <Button type="submit" :disabled="processing">
                                      <ShieldCheck />  {{ processing ? 'Enabling...' : 'Enable' }}
                                    </Button>
                                </Form>
                            </template>
                        </DialogTrigger>
                    </Dialog>
                </div>

                <div v-if="twoFactorEnabled" class="flex flex-col items-start justify-start space-y-4">
                    <Badge variant="default"> Enabled </Badge>
                    <p class="text-muted-foreground">
                        With two factor authentication enabled, you'll be prompted for a secure, random token during login, which you can retrieve
                        from your TOTP Authenticator app.
                    </p>
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex gap-3"><LockKeyhole class="size-4" /> 2FA Recovery Codes</CardTitle>
                            <CardDescription
                                >Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password
                                manager.</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="group flex items-center justify-between select-none">
                                <Button @click="toggleRecoveryCodesVisibility">
                                    <component :is="recoveryCodes.isVisible ? EyeOff : Eye" class="size-4" />
                                    {{ recoveryCodes.isVisible ? 'Hide' : 'View' }} Recovery Codes
                                </Button>
                                <Form
                                    v-if="recoveryCodes.isVisible"
                                    :action="route('two-factor.recovery-codes')"
                                    method="post"
                                    #default="{ processing }"
                                    :options="{
                                        preserveScroll: true,
                                    }"
                                    @success="
                                        () => {
                                            fetchRecoveryCodes().then((codes) => (recoveryCodes.list = codes));
                                        }
                                    "
                                >
                                    <Button variant="secondary" type="submit" :disabled="processing">
                                        <RefreshCw />
                                        {{ processing ? 'Regenerating...' : 'Regenerate Codes' }}
                                    </Button>
                                </Form>
                            </div>

                            <div
                                :class="[
                                    'relative overflow-hidden transition-all duration-300',
                                    recoveryCodes.isVisible ? 'h-auto opacity-100' : 'h-0 opacity-0',
                                ]"
                            >
                                <div class="mt-3 space-y-3">
                                    <div class="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm">
                                        <div v-for="(code, index) in recoveryCodes.list" :key="index">{{ code }}</div>
                                    </div>
                                    <p class="text-xs text-muted-foreground select-none">
                                        You have {{ recoveryCodes.list?.length }} recovery codes left. Each can be used once to access your account
                                        and will be removed after use. If you need more, click <span class="font-bold">Regenerate Codes</span> above.
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="relative inline">
                        <Form
                            :action="route('two-factor.disable')"
                            :async="false"
                            method="delete"
                            #default="{ processing }"
                            @success="disableTwoFactorAuthenticationSuccess"
                        >
                            <Button variant="destructive" type="submit" :disabled="processing">
                                <ShieldBan />
                                {{ processing ? 'Disabling...' : 'Disable 2FA' }}
                            </Button>
                        </Form>
                    </div>
                </div>

                <Dialog v-model:open="modalState.isOpen">
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader class="flex items-center justify-center">
                            <div class="mb-3 w-auto rounded-full border border-border bg-card p-0.5 shadow-sm">
                                <div class="relative overflow-hidden rounded-full border border-border bg-muted p-2.5">
                                    <div class="absolute inset-0 grid grid-cols-5 opacity-50">
                                        <div v-for="i in 5" :key="`col-${i}`" class="border-r border-border last:border-r-0"></div>
                                    </div>
                                    <div class="absolute inset-0 grid grid-rows-5 opacity-50">
                                        <div v-for="i in 5" :key="`row-${i}`" class="border-b border-border last:border-b-0"></div>
                                    </div>
                                    <ScanLine class="relative z-20 size-6 text-foreground" />
                                </div>
                            </div>
                            <DialogTitle>
                                {{ modalConfig.title }}
                            </DialogTitle>
                            <DialogDescription class="text-center">
                                {{ modalConfig.description }}
                            </DialogDescription>
                        </DialogHeader>

                        <div class="relative flex w-auto flex-col items-center justify-center space-y-5">
                            <template v-if="!modalState.isInVerificationStep">
                                <div class="relative mx-auto flex max-w-md items-center overflow-hidden">
                                    <div class="relative mx-auto aspect-square w-64 overflow-hidden rounded-lg border border-border">
                                        <div
                                            v-if="!setupData.qrCodeSvg"
                                            class="absolute inset-0 z-10 flex aspect-square h-auto w-full animate-pulse items-center justify-center bg-background"
                                        >
                                            <Loader2 class="size-6 animate-spin" />
                                        </div>
                                        <div v-else class="relative z-10 overflow-hidden border p-5">
                                            <div v-html="setupData.qrCodeSvg" class="flex aspect-square size-full items-center justify-center"></div>
                                            <div v-if="setupData.qrCodeSvg" class="animate-scanning-line absolute inset-0 h-full w-full">
                                                <div
                                                    class="absolute inset-x-0 h-0.5 bg-blue-500 opacity-60 transition-all duration-300 ease-in-out"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex w-full items-center space-x-5">
                                    <Button class="w-full" @click="handleSetupAction">
                                        {{ modalConfig.buttonText }}
                                    </Button>
                                </div>

                                <div class="relative flex w-full items-center justify-center">
                                    <div class="absolute inset-0 top-1/2 h-px w-full bg-border"></div>
                                    <span class="relative bg-card px-2 py-1"> or, enter the code manually </span>
                                </div>

                                <div class="flex w-full items-center justify-center space-x-2">
                                    <div class="flex w-full items-stretch overflow-hidden rounded-xl border border-border">
                                        <div v-if="!setupData.manualSetupKey" class="flex h-full w-full items-center justify-center bg-muted p-3">
                                            <Loader2 class="size-4 animate-spin" />
                                        </div>
                                        <template v-else>
                                            <input
                                                type="text"
                                                readonly
                                                :value="setupData.manualSetupKey"
                                                class="h-full w-full bg-background p-3 text-foreground"
                                            />
                                            <button
                                                @click="copyToClipboard(setupData.manualSetupKey || '')"
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
                                    v-slot="{ errors, processing }"
                                    reset-on-error
                                    @success="
                                        () => {
                                            modalState.isOpen = false;
                                            modalState.isInVerificationStep = false;
                                        }
                                    "
                                >
                                    <input type="hidden" name="code" :value="verificationCode" />
                                    <div ref="pinInputContainerRef" class="relative w-full space-y-3">
                                        <div class="flex w-full flex-col items-center justify-center space-y-3 py-2">
                                            <PinInput id="otp" placeholder="○" v-model="code" class="mt-1" type="number" otp>
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
                                            <InputError :message="errors?.confirmTwoFactorAuthentication?.code" />
                                        </div>

                                        <div class="flex w-full items-center space-x-5">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                class="w-auto flex-1"
                                                @click="modalState.isInVerificationStep = false"
                                                :disabled="processing"
                                            >
                                                Back
                                            </Button>
                                            <Button type="submit" class="w-auto flex-1" :disabled="processing || verificationCode.length < 6">
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
