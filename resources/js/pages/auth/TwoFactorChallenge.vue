<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PinInput, PinInputGroup, PinInputSlot } from '@/components/ui/pin-input';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { computed, ComputedRef, ref } from 'vue';

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
        toggleText: showRecoveryInput.value ? 'login using an authentication code' : 'login using a recovery code',
    };
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = [];
};
</script>

<template>
    <AuthLayout :title="authConfig.title" :description="authConfig.description">
        <Head title="Two Factor Authentication" />

        <div class="space-y-6">
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
                    <InputError :message="errors.code" />
                    <Button type="submit" class="w-full" :disabled="processing">Continue</Button>
                    <div class="text-center text-sm text-muted-foreground">
                        <span>or you can </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
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
                        placeholder="Enter recovery code"
                        :autofocus="showRecoveryInput"
                        required
                    />
                    <InputError :message="errors.recovery_code" />
                    <Button type="submit" class="w-full" :disabled="processing">Continue</Button>

                    <div class="text-center text-sm text-muted-foreground">
                        <span>or you can </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
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
