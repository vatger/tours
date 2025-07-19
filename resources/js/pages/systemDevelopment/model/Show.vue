<template>

    <AppLayout :breadcrumbs="breadcrumbs">

        <Head :title="$t('Show Model')" />
        <div class="space-y-4">

            <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">

                <div class="flex items-center justify-between mb-6">
                    <Heading :title="model.name" description="View devlopment system model" />
                    <div class="flex space-x-2">
                        <Button as-child>
                            <Link :href="route('models-generate.edit', { model: model.name })">
                            <PencilIcon class="w-4 h-4 mr-2" />
                            Edit
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="route('models-generate.export', { model: model.name, format: 'pdf' })">
                            <DownloadIcon class="w-4 h-4 mr-2" />
                            Export
                            </Link>
                        </Button>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- Model Configuration -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Model Configuration</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">

                                <div class="space-y-2 font-bold mb-2">
                                    <Label class="font-bold">{{ model.name }} - {{
                                        model.version }}</Label>
                                    <!-- <p class="mb-8 text-sm font-bold text-muted-foreground">{{ model.name }} - {{
                                        model.version }}</p> -->

                                </div>

                            </div>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div class="space-y-2">
                                    <Label>Features</Label>
                                    <ul class="space-y-2">
                                        <li v-if="model.softDeletes" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Soft Deletes</span>
                                        </li>
                                        <li v-if="model.timestamps" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Timestamps</span>
                                        </li>
                                        <li v-if="model.useIsActive" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Is Active</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="space-y-2">
                                    <Label>Additional Features</Label>
                                    <ul class="space-y-2">
                                        <li v-if="model.useApprovedStatus" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Approved Status</span>
                                        </li>
                                        <li v-if="model.useScribe" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Scribe Documentation</span>
                                        </li>
                                        <li v-if="model.authorize" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Authorization</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="space-y-2">
                                    <Label>Other Options</Label>
                                    <ul class="space-y-2">
                                        <li v-if="model.logsActivity" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Activity Logs</span>
                                        </li>
                                        <li v-if="model.clearsResponseCache" class="flex items-center space-x-2">
                                            <CheckIcon class="w-4 h-4 text-green-500" />
                                            <span>Clears Response Cache</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attributes Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Attributes ({{ model.attributes.length }})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Validation</TableHead>
                                        <TableHead>Options</TableHead>
                                        <TableHead>Description</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(attr, index) in model.attributes" :key="index">
                                        <TableCell class="font-medium">{{ attr.name }}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ attr.type }}</Badge>

                                            <span v-if="['string', 'text'].includes(attr.type)"
                                                class="ml-1 text-xs text-muted-foreground">
                                                ({{ attr.length }} min: {{ attr.min }} max: {{ attr.max }})
                                            </span>
                                            <span v-if="attr.type === 'decimal'"
                                                class="ml-1 text-xs text-muted-foreground">
                                                ({{ attr.precision }}, {{ attr.scale }})
                                            </span>

                                        </TableCell>

                                        <TableCell>
                                            <div class="flex flex-wrap gap-1">
                                                <Badge v-if="attr.required" variant="secondary">Required</Badge>
                                                <Badge v-if="attr.nullable" variant="secondary">Nullable</Badge>
                                                <Badge v-if="attr.unique" variant="secondary">Unique</Badge>
                                                <Badge v-if="attr.validate" variant="secondary">Validated</Badge>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-wrap gap-1">
                                                <Badge v-if="attr.sortAble" variant="outline">Sortable</Badge>
                                                <Badge v-if="attr.filterAble" variant="outline">Filterable</Badge>
                                                <Badge v-if="attr.exactFilter" variant="outline">Exact Filter</Badge>
                                                <Badge v-if="attr.searchAble" variant="outline">Searchable</Badge>
                                                <Badge v-if="attr.translated" variant="outline">Translated</Badge>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <template v-if="attr.description">
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <p class="text-sm truncate max-w-[200px]">
                                                            {{ attr.description.length > 32 ?
                                                                attr.description.substring(0,
                                                                    32) + '...' : attr.description }}
                                                        </p>
                                                    </TooltipTrigger>
                                                    <TooltipContent v-if="attr.description.length > 32">
                                                        <p class="max-w-[300px]">{{ attr.description }}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </template>
                                            <p v-else class="text-sm text-muted-foreground">No description</p>

                                            <p v-if="attr.example" class="text-xs text-muted-foreground">
                                                Example: {{ attr.example }}
                                            </p>
                                        </TableCell>

                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <!-- Relations Section -->
                    <Card v-if="model.relations && model.relations.length">
                        <CardHeader>
                            <CardTitle class="text-base">Relationships ({{ model.relations.length }})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Related Model</TableHead>
                                        <TableHead>Description</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(rel, index) in model.relations" :key="index">
                                        <TableCell class="font-medium">{{ rel.name }}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {{ formatRelationType(rel.type) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{{ rel.related }}</TableCell>
                                        <TableCell>
                                            <template v-if="rel.description">
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <p class="text-sm truncate max-w-[200px]">
                                                            {{ rel.description.length > 64 ?
                                                                rel.description.substring(0,
                                                                    64) + '...' : rel.description }}
                                                        </p>
                                                    </TooltipTrigger>
                                                    <TooltipContent v-if="rel.description.length > 64">
                                                        <p class="max-w-[300px]">{{ rel.description }}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </template>
                                            <p v-else class="text-sm text-muted-foreground">No description</p>


                                        </TableCell>

                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <!-- Metadata -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Metadata</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Created At</Label>
                                    <p>{{ formatDateTime(model.created_at) }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label>Last Updated</Label>
                                    <p>{{ formatDateTime(model.updated_at) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { PencilIcon, DownloadIcon, CheckIcon } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card'
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import { Label } from '@/components/ui/label'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table'
import Heading from '@/components/Heading.vue'

import AppLayout from '@/layouts/AppLayout.vue'
const props = defineProps({
    model: {
        type: Object,
        required: true
    }
})

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Models', href: route('models-generate.index') },
    { title: props.model.name, href: '#' }
]

const formatRelationType = (type) => {
    const types = {
        'belongsTo': 'Belongs To',
        'hasOne': 'Has One',
        'hasMany': 'Has Many',
        'belongsToMany': 'Belongs To Many'
    }
    return types[type] || type
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    return date.toLocaleString()
}
</script>
