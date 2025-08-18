<script setup lang="ts">
import { ref, watch, nextTick, computed } from 'vue';
import { Head, Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { PinInputRoot, PinInputInput } from 'reka-ui';

const recovery = ref(false);
const pinValue = ref<string[]>([]);
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
})

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
    :description="recovery
      ? 'Please confirm access to your account by entering one of your emergency recovery codes.'
      : 'Enter the authentication code provided by your authenticator application.'"
  >
    <Head title="Two Factor Authentication" />

    <div class="relative space-y-2">
      <template v-if="!recovery">
        <Form
          :action="route('two-factor.login')"
          method="post"
          class="space-y-4"
          #default="{ errors, processing, clearErrors }"
        >
          <input type="hidden" name="code" :value="codeValue" />
          <div class="flex flex-col items-center justify-center space-y-3 text-center">
            <div ref="pinInputContainerRef" class="w-full flex items-center justify-center">
              <PinInputRoot
                id="otp"
                v-model="pinValue"
                placeholder="○"
                class="flex gap-2 items-center mt-1"
              >
                <PinInputInput
                  v-for="(id, index) in 6"
                  :key="id"
                  :index="index"
                  :autofocus="index === 0"
                  class="w-10 h-10 rounded-lg text-center shadow-sm border text-green10 placeholder:text-mauve8 focus:shadow-[0_0_0_2px] focus:shadow-stone-800 outline-none"
                />
              </PinInputRoot>
            </div>
            <InputError :message="errors.code" />
          </div>
          <Button
            type="submit"
            class="w-full"
            :disabled="processing || pinValue.length !== 6"
          >
            Continue
          </Button>

          <div class="space-x-0.5 text-center text-sm leading-5 text-muted-foreground mt-4">
            <span class="opacity-80">or you can </span>
            <button
              type="button"
              class="font-medium underline opacity-80 cursor-pointer bg-transparent border-0 p-0"
              @click="() => toggleRecovery(clearErrors)"
            >
              login using a recovery code
            </button>
          </div>
        </Form>
      </template>

      <template v-else>
        <Form
          :action="route('two-factor.login')"
          method="post"
          class="space-y-4"
          #default="{ errors, processing, clearErrors }"
        >
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
          <Button type="submit" class="w-full" :disabled="processing">
            Continue
          </Button>

          <div class="space-x-0.5 text-center text-sm leading-5 text-muted-foreground mt-4">
            <span class="opacity-80">or you can </span>
            <button
              type="button"
              class="font-medium underline opacity-80 cursor-pointer bg-transparent border-0 p-0"
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
