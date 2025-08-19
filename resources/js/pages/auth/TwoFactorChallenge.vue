<script setup lang="ts">
import { computed, ComputedRef, nextTick, ref, watch } from 'vue';
import { Head, Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { PinInput, PinInputGroup, PinInputSlot } from '@/components/ui/pin-input';

const showRecoveryInput = ref(false);
const code = ref<number[]>([]);
const recoveryInputRef = ref<HTMLInputElement | null>(null);

const codeValue: ComputedRef<string> = computed(() => code.value.join(''));

const authConfig = computed(() => {
    return {
        title: showRecoveryInput.value ? 'Recovery Code' : 'Authentication Code',
        description: showRecoveryInput.value
            ? 'Please confirm access to your account by entering one of your emergency recovery codes.'
            : 'Enter the authentication code provided by your authenticator application.',
        toggleText: showRecoveryInput.value
            ? 'login using an authentication code'
            : 'login using a recovery code',
    };
});

watch(
    showRecoveryInput,
    (isRecoveryMode) => {
        nextTick(() => {
            if (isRecoveryMode && recoveryInputRef.value) {
                recoveryInputRef.value.focus();
            }
        });
    },
);

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = [];
};</script>

<template>
    <AuthLayout :title="authConfig.title" :description="authConfig.description">
        <Head title="Two Factor Authentication" />

        <div class="relative space-y-2">
            <template v-if="!showRecoveryInput">
                <Form :action="route('two-factor.login')" method="post" class="space-y-4" #default="{ errors, processing, clearErrors }">
                    <input type="hidden" name="code" :value="codeValue" />
                    <div class="flex flex-col items-center justify-center space-y-3 text-center">
                        <div class="flex w-full items-center justify-center">
                            <PinInput id="otp" v-model="code" class="mt-1" type="number" otp>
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
                    <Button type="submit" class="w-full" :disabled="processing || code.length !== 6"> Continue </Button>

                    <div class="mt-4 space-x-0.5 text-center text-sm leading-5 text-muted-foreground">
                        <span class="opacity-80">or you can </span>
                        <button
                            type="button"
                            class="cursor-pointer border-0 bg-transparent p-0 font-medium underline opacity-80"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfig.toggleText }}
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
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfig.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>
