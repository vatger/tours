<template>
    <div class="space-y-8" v-if="model">
        <!-- Model Configuration -->
        <Card>
            <CardHeader>
                <CardTitle class="text-base">Model Configuration</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label>Name</Label>
                        <p class="font-medium">{{ model.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Version</Label>
                        <p class="font-medium">{{ model.version }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mt-6">
                    <!-- Features -->
                    <div class="space-y-4">
                        <Label>Features</Label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <Checkbox :checked="model.softDeletes" disabled />
                                <Label>Soft Deletes</Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox :checked="model.timestamps" disabled />
                                <Label>Timestamps</Label>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Features -->
                    <div class="space-y-4">
                        <Label>Additional Features</Label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <Checkbox :checked="model.useScribe" disabled />
                                <Label>Use Scribe</Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox :checked="model.authorize" disabled />
                                <Label>Authorization</Label>
                            </div>
                        </div>
                    </div>

                    <!-- Other Options -->
                    <div class="space-y-4">
                        <Label>Other Options</Label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <Checkbox :checked="model.logsActivity" disabled />
                                <Label>Logs Activity</Label>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Attributes -->
        <Card>
            <CardHeader>
                <CardTitle class="text-base">Attributes ({{ model.attributes?.length || 0 }})</CardTitle>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>Validation</TableHead>
                            <TableHead>Options</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(attr, index) in model.attributes" :key="index">
                            <TableCell class="font-medium">{{ attr.name }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ attr.type }}</Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-if="attr.required" variant="secondary">Required</Badge>
                                    <Badge v-if="attr.nullable" variant="secondary">Nullable</Badge>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-if="attr.unique" variant="outline">Unique</Badge>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- Relations -->
        <Card v-if="model.relations?.length">
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
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(rel, index) in model.relations" :key="index">
                            <TableCell class="font-medium">{{ rel.name }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ formatRelationType(rel.type) }}</Badge>
                            </TableCell>
                            <TableCell>{{ rel.related }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox'
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'

const props = defineProps({
    model: {
        type: Object,
        required: true
    }
})


const formatRelationType = (type: string) => {
    const types: Record<string, string> = {
        'belongsTo': 'Belongs To',
        'hasOne': 'Has One',
        'hasMany': 'Has Many',
        'belongsToMany': 'Belongs To Many'
    }
    return types[type] || type
}
</script>
