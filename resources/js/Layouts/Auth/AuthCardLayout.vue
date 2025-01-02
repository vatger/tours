<!-- AuthCardLayout.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';

interface Props {
    title?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: undefined,
    description: undefined
});

const page = usePage();
const name = page.props.name;
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-6">
            <Link :href="route('home')" class="flex items-center gap-2 self-center font-medium">
                <div class="flex h-10 w-10 items-center justify-center">
                    <ApplicationLogo class="size-10 fill-current text-black" />
                </div>
            </Link>

            <div class="flex flex-col gap-6">
                <Card>
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
