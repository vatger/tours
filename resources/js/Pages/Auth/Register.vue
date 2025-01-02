<script setup lang="ts">
import { useForm, Link, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { LoaderCircle } from 'lucide-vue-next';
import AuthBase from '@/Layouts/Auth/AuthBase.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase 
        title="Create an account"
        description="Enter your information below to create your account"
    >
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        v-model="form.name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        v-model="form.email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        v-model="form.password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        v-model="form.password_confirmation"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button 
                    type="submit" 
                    class="w-full" 
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create Account
                </Button>

                <hr />
            </div>

            <div class="text-center text-sm">
                Already have an account? 
                <Link 
                    :href="route('login')" 
                    class="underline underline-offset-4"
                >
                    Log in
                </Link>
            </div>
        </form>
    </AuthBase>
</template>
