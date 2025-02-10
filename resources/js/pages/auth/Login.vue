<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox'
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input id="email" type="email" required autofocus tabindex="1" autocomplete="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" tabindex="5">
                            Forgot your password?
                        </TextLink>
                    </div>
                    <Input id="password" type="password" required tabindex="2" autocomplete="current-password" v-model="form.password" />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Uncomment this below to enable remember me checkbox -->
                <!-- <div class="flex items-center justify-between">
                    <Label for="remember" class="space-x-1.5 h-full flex items-center">
                        <Checkbox id="remember" v-model:checked="form.remember" tabindex="4" />
                        <span>Remember me</span>
                    </Label>
                </div> -->

                <Button type="submit" class="w-full" tabindex="3" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Log In
                </Button>
            </div>

            <div class="text-center text-sm">
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="4">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
