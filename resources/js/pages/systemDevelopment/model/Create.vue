<template>


    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Create Model" />
        <div class="px-4 py-6">

            <div>
                <Heading title="Create a Model" description="Define your data structure and properties " />

                <Card>
                    <CardHeader>
                        <CardTitle>
                        </CardTitle>
                        <!-- <CardDescription>
                        Define your data structure and properties
                    </CardDescription> -->
                    </CardHeader>
                    <CardContent>
                        <ModelForm v-model="form" :errors="errors" :is-editing="false" @submit="submit" />
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
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Models', href: route('models-generate.index') },
    { title: 'Create', href: '#' }
]

const form = useForm({
    name: '',
    version: 'V1',
    softDeletes: true,
    timestamps: true,
    useIsActive: false,
    useApprovedStatus: false,
    useScribe: false,
    authorize: false,
    logsActivity: false,
    clearsResponseCache: false,
    attributes: [
        {
            name: '',
            type: 'string',
            length: 255,
            max: 255,
            min: 0,
            precision: 0,
            scale: 0,
            validate: true,
            required: false,
            nullable: true,
            unique: false,
            translated: false,
            sortAble: false,
            filterAble: false,
            exactFilter: false,
            searchAble: false,
            description: '',
            example: ''
        }
    ],
    relations: []
})

const submit = () => {
    form.post(route('models-generate.store'), {
        onSuccess: () => form.reset()
    })
}
</script>
