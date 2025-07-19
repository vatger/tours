<template>


    <AppLayout :breadcrumbs="breadcrumbs">

        <Head :title="$t('Edit Model')" />
        <div class="space-y-4">

            <div class="flex items-center justify-between">
                <Heading title="Edit Model" description="Update your data structure and properties" />
                <Card>
                    <CardHeader>
                        <CardTitle>{{ model.name }}</CardTitle>
                        <!-- <CardDescription>
                        Update your data structure and properties
                    </CardDescription> -->
                    </CardHeader>
                    <CardContent>
                        <ModelForm v-model="form" :errors="errors" :is-editing="true" @submit="submit" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import ModelForm from './partials/ModelForm.vue'
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card'
import Heading from '@/components/Heading.vue'

import AppLayout from '@/layouts/AppLayout.vue';
const props = defineProps({
    model: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Models', href: route('models-generate.index') },
    { title: 'Edit', href: '#' }
]

const form = useForm({
    name: props.model.name,
    version: props.model.version || 'V1',
    softDeletes: props.model.softDeletes ?? true,
    timestamps: props.model.timestamps ?? true,
    useIsActive: props.model.useIsActive ?? false,
    useApprovedStatus: props.model.useApprovedStatus ?? false,
    useScribe: props.model.useScribe ?? false,
    authorize: props.model.authorize ?? false,
    logsActivity: props.model.logsActivity ?? false,
    clearsResponseCache: props.model.clearsResponseCache ?? false,
    attributes: props.model.attributes?.length ? props.model.attributes : [
        {
            name: 'id',
            type: 'string',
            length: 255,
            max: 255,
            min: 0,
            precision: 0,
            scale: 0,
            validate: true,
            required: true,
            nullable: false,
            unique: true,
            translated: false,
            sortAble: true,
            filterAble: true,
            exactFilter: false,
            searchAble: false,
            description: 'The unique identifier',
            example: ''
        }
    ],
    relations: props.model.relations || []
})

const submit = () => {
    form.put(route('models-generate.update', { model: props.model.name }), {
        onSuccess: () => {
            // Optional: Add any success handling
        }
    })
}
</script>
