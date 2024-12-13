<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { cn } from '@/utils'
import { ref } from 'vue'
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
    name?: string;
    quote?: object;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>


<template>
    <AuthLayout :name="name" :quote="quote">
        <Head title="Login" />
        <template v-slot:topRight>
            <Button variant="ghost" as-child>
                <Link :href="route('register')">
                    Register
                </Link>
            </Button>
        </template>

        
        <div class="flex flex-col space-y-2 text-center">
            
            <h1 class="text-2xl font-semibold tracking-tight">
                Login to your account
            </h1>
            <p class="text-sm text-muted-foreground">
                Enter your email and password below to login
            </p>
        </div>
        <div :class="cn('grid gap-6', $attrs.class ?? '')">
            <form @submit.prevent="submit">
                <div class="grid gap-3">
                    
                    <div>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            placeholder="Email Address"
                            required
                            autofocus
                            autocomplete="email"
                            :disabled="form.processing"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <Input
                            id="password"
                            type="password"
                            placeholder="Password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            :disabled="form.processing"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                    
                    <Button :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Login to your account
                    </Button>
                </div>
            </form>
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-gray-200" />
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-background hidden px-2 text-muted-foreground">
                        Forgot Password
                    </span>
                </div>
            </div>
        </div>
        <p class="px-8 text-center text-sm text-muted-foreground">
            Forgot your password?
            <a href="/reset" class="underline underline-offset-4 hover:text-primary">
                Click here to reset
            </a>
        </p>
    </AuthLayout>
</template>