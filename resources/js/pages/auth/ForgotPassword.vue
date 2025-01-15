<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Forgot Password" description="Enter your email to receive a password reset link">
        <Head title="Forgot Password" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" v-model="form.email" autofocus />

                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Email Password Reset Link
                    </Button>
                </div>
            </form>
            <div class="space-x-1 text-center text-sm">
                <span>Or, return to the</span>
                <Link :href="route('login')" class="underline underline-offset-4"> login page </Link>
            </div>
        </div>
    </AuthLayout>
</template>
