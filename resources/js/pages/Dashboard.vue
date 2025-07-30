<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!--        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">-->
        <!--            <div class="grid auto-rows-min gap-4 md:grid-cols-3">-->
        <!--                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">-->
        <!--                    <PlaceholderPattern />-->

        <!--                    testasda-->
        <!--                </div>-->
        <!--                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">-->
        <!--                    <PlaceholderPattern />-->
        <!--                </div>-->
        <!--                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">-->
        <!--                    <PlaceholderPattern />-->
        <!--                </div>-->
        <!--            </div>-->
        <!--            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">-->
        <!--                <PlaceholderPattern />-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="bg-slate-400 h-full rounded-xl p-4 gap-4 text-center">-->
        <!--            <div>1</div>-->
        <!--            <div>2</div>-->
        <!--            <div>3</div>-->
        <!--            <div>4</div>-->
        <!--        </div>-->
        <div class="h-screen w-full bg-slate-200 bg-cover bg-center p-20">
            <div class="">
                <div class="flex flex-col items-center justify-center">
                    <div class="pb-5">
                        <Avatar class="size-48 border shadow-2xl shadow-slate-400">
                            <AvatarImage src="https://vamosmobile.app/sccdrrmo/flutter/images/ZJNXJG5162_68661a2d3c45b.jpeg" alt="@unovue" />
                            <AvatarFallback class="text-3xl">JK</AvatarFallback>
                        </Avatar>
                    </div>

                    <Label class=" text-center text-3xl">Jerhod Kyan Aburido Ricabo</Label>

                    <div class="vertical:flex-col mt-10 flex flex-wrap items-center justify-center gap-2">
                        <Button variant="ghost" class="size-48 duration-200 hover:scale-110">
                            <img
                                src="https://vamosmobile.app/scims/dashboard/images/webp/dashboard_icons/DOCTRACK.webp"
                                alt="Image"
                                class="w-36 sm:w-16 md:w-32 lg:w-48"
                            />
                        </Button> <Button variant="ghost" class="size-48 duration-200 hover:scale-110">
                            <img
                                src="https://vamosmobile.app/scims/dashboard/images/webp/dashboard_icons/DOCTRACK.webp"
                                alt="Image"
                                class="w-36 sm:w-16 md:w-32 lg:w-48"
                            />
                        </Button> <Button variant="ghost" class="size-48 duration-200 hover:scale-110">
                            <img
                                src="https://vamosmobile.app/scims/dashboard/images/webp/dashboard_icons/DOCTRACK.webp"
                                alt="Image"
                                class="w-36 sm:w-16 md:w-32 lg:w-48"
                            />
                        </Button> <Button variant="ghost" class="size-48 duration-200 hover:scale-110">
                            <img
                                src="https://vamosmobile.app/scims/dashboard/images/webp/dashboard_icons/DOCTRACK.webp"
                                alt="Image"
                                class="w-36 sm:w-16 md:w-32 lg:w-48"
                            />
                        </Button> <Button variant="ghost" class="size-48 duration-200 hover:scale-110">
                            <img
                                src="https://vamosmobile.app/scims/dashboard/images/webp/dashboard_icons/DOCTRACK.webp"
                                alt="Image"
                                class="w-36 sm:w-16 md:w-32 lg:w-48"
                            />
                        </Button> <Button variant="ghost" class="size-48 duration-200 hover:scale-110">
                            <img
                                src="https://vamosmobile.app/scims/dashboard/images/webp/dashboard_icons/DOCTRACK.webp"
                                alt="Image"
                                class="w-36 sm:w-16 md:w-32 lg:w-48"
                            />
                        </Button> <Button variant="ghost" class="size-48 duration-200 hover:scale-110">
                            <img
                                src="https://vamosmobile.app/scims/dashboard/images/webp/dashboard_icons/DOCTRACK.webp"
                                alt="Image"
                                class="w-36 sm:w-16 md:w-32 lg:w-48"
                            />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
