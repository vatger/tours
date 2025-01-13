<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://cdn.devdojo.com/assets/svg/laravel-vue-logo.svg" width="300" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/vue-starter-kit/actions"><img src="https://github.com/laravel/vue-starter-kit/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

<img src="https://cdn.devdojo.com/images/december2024/screenshot.png" />

## Introduction

Welcome to the Laravel **Vue Starter Kit**, a starter kit built using [Laravel](https://laravel.com), [Vue](https://vuejs.org), [Inertia](https://inertiajs.com), and [Tailwind CSS](https://tailwindcss.com).

## Installation

To install the starter kit, run the following command:

1. git clone https://github.com/laravel/vue-starter-kit
2. cd vue-starter-kit
3. git checkout develop
3. copy .env.example .env
4. install dependencies `npm install && composer install`
5. run migrations `php artisan migrate`
6. add encryption key `php artisan key:generate`
7. start the asset watcher `npm run dev`

Visit the URL for your app and you're good to go!

## Features

This Starter Kit includes the following features:

 - **User Authentication** (login, register, password reset, email verify, and password confirmation)
 - **Dashboard Page** (Auth Protected User Dashboard Page)
 - **Settings Page** (Profile Update/Delete, Password Update, Appearance)

## Front-end App Structure

The majority of the front-end code is located in the `resources/js` folder. We follow Vue.js best practices and conventions for organizing these files and folders. The structure follows these naming conventions:

**Folders**: Use kebab-case
```
resources/js/
├── components/     # Reusable Vue components
├── composables/    # Vue composables/hooks
├── layouts/        # Application layouts
├── lib/           # Utility functions and configurations
├── pages/         # Page components
└── types/         # Typescript definitions and interfaces
```

**Components**: Use PascalCase for component files
```
components/
└── AppearanceTabs.vue
└── NavigationBar.vue
```

**Composables/Utilities**: Use camelCase for utility files and composables
```
composables/
└── useAuth.ts
└── useSettings.ts
```

This structure aligns more with the default Vue conventions.

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