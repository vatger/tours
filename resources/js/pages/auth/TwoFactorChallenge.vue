<script setup lang="ts">
import { ref, watch, nextTick, computed } from 'vue';
import { Head, Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { PinInput, PinInputGroup, PinInputSlot } from '@/components/ui/pin-input';

const recovery = ref(false);
const pinValue = ref<number[]>([]);
const pinInputContainerRef = ref<HTMLElement | null>(null);
const recoveryInputRef = ref<HTMLInputElement | null>(null);

const codeValue = computed(() => pinValue.value.join(''));

watch(recovery, (value) => {
    if (value) {
        nextTick(() => {
            if (recoveryInputRef.value) {
                recoveryInputRef.value?.focus();
            }
        });
    } else {
        nextTick(() => {
            if (pinInputContainerRef.value) {
                const firstInput = pinInputContainerRef.value.querySelector('input');
                if (firstInput) firstInput.focus();
            }
        });
    }
});

const toggleRecovery = (clearErrors: () => void) => {
    recovery.value = !recovery.value;
    clearErrors();
    pinValue.value = [];
    const codeInput = document.querySelector('input[name="code"]') as HTMLInputElement;
    const recoveryInput = document.querySelector('input[name="recovery_code"]') as HTMLInputElement;
    if (codeInput) codeInput.value = '';
    if (recoveryInput) recoveryInput.value = '';
};
</script>

<template>
    <AuthLayout
        :title="recovery ? 'Recovery Code' : 'Authentication Code'"
        :description="
            recovery
                ? 'Please confirm access to your account by entering one of your emergency recovery codes.'
                : 'Enter the authentication code provided by your authenticator application.'
        "
    >
        <Head title="Two Factor Authentication" />

        <div class="relative space-y-2">
            <template v-if="!recovery">
                <Form :action="route('two-factor.login')" method="post" class="space-y-4" #default="{ errors, processing, clearErrors }">
                    <input type="hidden" name="code" :value="codeValue" />
                    <div class="flex flex-col items-center justify-center space-y-3 text-center">
                        <div ref="pinInputContainerRef" class="flex w-full items-center justify-center">
                            <PinInput id="otp" v-model="pinValue" class="mt-1" type="number" otp>
                                <PinInputGroup class="gap-2">
                                    <PinInputSlot
                                        v-for="(id, index) in 6"
                                        :key="id"
                                        :index="index"
                                        :autofocus="index === 0"
                                        class="h-10 w-10 rounded-lg"
                                    />
                                </PinInputGroup>
                            </PinInput>
                        </div>
                        <InputError :message="errors.code" />
                    </div>
                    <Button type="submit" class="w-full" :disabled="processing || pinValue.length !== 6"> Continue </Button>

                    <div class="mt-4 space-x-0.5 text-center text-sm leading-5 text-muted-foreground">
                        <span class="opacity-80">or you can </span>
                        <button
                            type="button"
                            class="cursor-pointer border-0 bg-transparent p-0 font-medium underline opacity-80"
                            @click="() => toggleRecovery(clearErrors)"
                        >
                            login using a recovery code
                        </button>
                    </div>
                </Form>
            </template>

            <template v-else>
                <Form :action="route('two-factor.login')" method="post" class="space-y-4" #default="{ errors, processing, clearErrors }">
                    <Input
                        ref="recoveryInputRef"
                        name="recovery_code"
                        type="text"
                        class="block w-full"
                        placeholder="Enter recovery code"
                        autofocus
                        required
                    />
                    <InputError :message="errors.recovery_code" />
                    <Button type="submit" class="w-full" :disabled="processing"> Continue </Button>

                    <div class="mt-4 space-x-0.5 text-center text-sm leading-5 text-muted-foreground">
                        <span class="opacity-80">or you can </span>
                        <button
                            type="button"
                            class="cursor-pointer border-0 bg-transparent p-0 font-medium underline opacity-80"
                            @click="() => toggleRecovery(clearErrors)"
                        >
                            login using an authentication code
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>
