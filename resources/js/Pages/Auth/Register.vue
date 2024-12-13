<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/Components/InputError.vue';
import { Label } from '@/components/ui/label'
import { cn } from '@/utils'
import { ref } from 'vue'
import { LoaderCircle } from 'lucide-vue-next';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

defineProps<{
    name?: string;
    quote?: object;
}>();

async function submit(event: Event) {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
}
</script>

<template>
    <AuthLayout :name="name" :quote="quote">
        <Head title="Register" />
        <template v-slot:topRight>
            <Button variant="ghost" as-child>
                <Link :href="route('login')">
                    Login
                </Link>
            </Button>
            
        </template>
        <div class="flex flex-col items-center space-y-2 text-center">
            <h1 class="text-2xl font-semibold tracking-tight">
                Create an account
            </h1>
            <p class="text-sm text-muted-foreground">
                Enter your name, email and password below.
            </p>
        </div>
        <div :class="cn('grid gap-6', $attrs.class ?? '')">
            <form @submit.prevent="submit">
                <div class="grid gap-3">
                    
                    <div>
                        <Input
                            id="name"
                            type="text"
                            v-model="form.name"
                            placeholder="Name"
                            required
                            autofocus
                            autocomplete="name"
                            :disabled="form.processing"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            placeholder="Email Address"
                            required
                            autocomplete="email"
                            :disabled="form.processing"
                        />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <Input
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            placeholder="Password"
                            required
                            autocomplete="new-password"
                            :disabled="form.processing"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <Input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            placeholder="Confirm Password"
                            required
                            autocomplete="new-password"
                            :disabled="form.processing"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                    
                    <Button :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Register
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
        <p class="px-8 text-center text-sm text-muted-foreground whitespace-nowrap">
            Already Registered?
            <Link
                :href="route('login')"
                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >Login here</Link>
        </p>
    </AuthLayout>
</template>
