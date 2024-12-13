<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Vue Starter Kit

Welcome to the Laravel **Vue Starter Kit**, a starter kit built using [Laravel](https://laravel.com), [Vue](https://vuejs.org), [Inertia](https://inertiajs.com), and [Tailwind CSS](https://tailwindcss.com).

## Installation

To install the starter kit, run the following command:

1. git clone https://github.com/laravel/vue-starter-kit
2. cd vue-starter-kit
3. copy .env.example .env
4. install dependencies `npm install && composer install`
5. run migrations `php artisan migrate`
6. add encryption key `php artisan key:generate`
7. start the asset watcher `npm run dev`

Visit the URL for your app and you're good to go!

## Icons

This starter kit leverages the [Lucide Vue Library](https://lucide.dev/guide/packages/lucide-vue-next), which provides you with a collection of over 1000 icons. View the full list of icons [here](https://lucide.dev/icons).

Here's a quick example of using an icon in one of your Vue Components:

```
<script setup lang="ts">
    ...
    import { Rocket } from 'lucide-vue-next';
    ...
</script>

<template>
    <p class="flex items-center space-x-2">
        <Rocket />
        <span class="text-lg font-medium">Vue Starter Kit</span>
    </p>
</template>
```