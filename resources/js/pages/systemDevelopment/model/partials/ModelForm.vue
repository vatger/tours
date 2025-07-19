<template>
    <form @submit.prevent="$emit('submit')">
        <div class="flex-1 p-2 space-y-8 overflow-y-auto">
            <div>
                <h3 class="text-lg font-semibold">Model Configuration</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="name">Model Name</Label>
                        <Input id="name" v-model="form.name" placeholder="User, Product, etc." :disabled="isEditing" />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="version">Version</Label>
                        <Input id="version" v-model="form.version" placeholder="1.0, 2.0, etc." />
                        <InputError class="mt-2" :message="errors.version" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 mt-4">
                    <div class="space-y-4">
                        <Label>Features</Label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <Checkbox id="softDeletes" v-model="form.softDeletes" />
                                <Label for="softDeletes">Soft Deletes</Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox id="timestamps" v-model="form.timestamps" />
                                <Label for="timestamps">Timestamps</Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox id="useIsActive" v-model="form.useIsActive" />
                                <Label for="useIsActive">Use Is Active</Label>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <Label class="opacity-0">Features</Label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <Checkbox id="useApprovedStatus" v-model="form.useApprovedStatus" />
                                <Label for="useApprovedStatus">Use Approved Status</Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox id="useScribe" v-model="form.useScribe" />
                                <Label for="useScribe">Use Scribe</Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox id="authorize" v-model="form.authorize" />
                                <Label for="authorize">Authorization</Label>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <Label class="opacity-0">Features</Label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <Checkbox id="logsActivity" v-model="form.logsActivity" />
                                <Label for="logsActivity">Logs Activity</Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox id="clearsResponseCache" v-model="form.clearsResponseCache" />
                                <Label for="clearsResponseCache">Clears Response Cache</Label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Attributes</h3>
                    <Button type="button" variant="outline" size="sm" @click="addAttribute">
                        Add Attribute
                    </Button>
                </div>

                <div v-for="(attr, index) in form.attributes" :key="index"
                    class="p-4 space-y-4 border rounded-lg bg-card">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium">Attribute #{{ index + 1 }}</h4>
                        <div class="flex gap-2">
                            <Button type="button" variant="ghost" size="sm" class="w-8 h-8 p-0" @click="addAttribute">
                                <PlusIcon class="w-4 h-4" />
                            </Button>
                            <Button type="button" variant="ghost" size="sm"
                                class="w-8 h-8 p-0 text-destructive hover:text-destructive"
                                @click="removeAttribute(index)">
                                <TrashIcon class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div class="col-span-2 space-y-2">
                            <Label :for="`attr-name-${index}`">Name</Label>
                            <div class="flex gap-2">
                                <Input :id="`attr-name-${index}`" v-model="attr.name" placeholder="name" />
                                <Select :id="`attr-type-${index}`" v-model="attr.type">
                                    <SelectTrigger class="w-[140px]">
                                        <SelectValue placeholder="Type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem v-for="type in attributeTypes" :key="type.value"
                                                :value="type.value">
                                                {{ type.label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <template v-if="['string', 'text'].includes(attr.type)">
                            <div class="space-y-2">
                                <Label :for="`attr-length-${index}`">Length</Label>
                                <Input :id="`attr-length-${index}`" v-model.number="attr.length" type="number" />
                            </div>
                            <div class="space-y-2">
                                <Label :for="`attr-max-${index}`">Max</Label>
                                <Input :id="`attr-max-${index}`" v-model.number="attr.max" type="number" />
                            </div>
                            <div class="space-y-2">
                                <Label :for="`attr-min-${index}`">Min</Label>
                                <Input :id="`attr-min-${index}`" v-model.number="attr.min" type="number" />
                            </div>
                        </template>

                        <template v-if="['decimal'].includes(attr.type)">
                            <div class="space-y-2">
                                <Label :for="`attr-precision-${index}`">Precision</Label>
                                <Input :id="`attr-precision-${index}`" v-model.number="attr.precision" type="number" />
                            </div>
                            <div class="space-y-2">
                                <Label :for="`attr-scale-${index}`">Scale</Label>
                                <Input :id="`attr-scale-${index}`" v-model.number="attr.scale" type="number" />
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <Label>Validation</Label>
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-validate-${index}`" v-model="attr.validate" />
                                    <Label for="attr-validate-{{index}}">Validate</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-required-${index}`" v-model="attr.required" />
                                    <Label for="attr-required-{{index}}">Required</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-nullable-${index}`" v-model="attr.nullable" />
                                    <Label for="attr-nullable-{{index}}">Nullable</Label>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Options</Label>
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-unique-${index}`" v-model="attr.unique" />
                                    <Label for="attr-unique-{{index}}">Unique</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-translated-${index}`" v-model="attr.translated" />
                                    <Label for="attr-translated-{{index}}">Translated</Label>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Filter/Sort</Label>
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-sortAble-${index}`" v-model="attr.sortAble" />
                                    <Label for="attr-sortAble-{{index}}">Sort</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-filterAble-${index}`" v-model="attr.filterAble" />
                                    <Label for="attr-filterAble-{{index}}">Filter</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox :id="`attr-exactFilter-${index}`" v-model="attr.exactFilter" />
                                    <Label for="attr-exactFilter-{{index}}">Exact</Label>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Search</Label>
                            <div class="flex items-center space-x-2">
                                <Checkbox :id="`attr-searchAble-${index}`" v-model="attr.searchAble" />
                                <Label for="attr-searchAble-{{index}}">Searchable</Label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label :for="`attr-description-${index}`">Description</Label>
                            <Textarea :id="`attr-description-${index}`" v-model="attr.description"
                                placeholder="What this attribute represents" />
                        </div>
                        <div class="space-y-2">
                            <Label :for="`attr-example-${index}`">Example</Label>
                            <Input :id="`attr-example-${index}`" v-model="attr.example" placeholder="Example" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Relationships</h3>
                    <Button type="button" variant="outline" size="sm" @click="addRelation">
                        Add Relationship
                    </Button>
                </div>

                <div v-for="(rel, index) in form.relations" :key="index"
                    class="p-4 space-y-4 border rounded-lg bg-card">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium">Relationship #{{ index + 1 }}</h4>
                        <div class="flex gap-2">
                            <Button type="button" variant="ghost" size="sm" class="w-8 h-8 p-0" @click="addRelation">
                                <PlusIcon class="w-4 h-4" />
                            </Button>
                            <Button type="button" variant="ghost" size="sm"
                                class="w-8 h-8 p-0 text-destructive hover:text-destructive"
                                @click="removeRelation(index)">
                                <TrashIcon class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <Label :for="`rel-name-${index}`">Name</Label>
                            <Input :id="`rel-name-${index}`" v-model="rel.name" placeholder="user, posts" />
                        </div>

                        <div class="space-y-2">
                            <Label :for="`rel-type-${index}`">Type</Label>
                            <Select :id="`rel-type-${index}`" v-model="rel.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="type in relationTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label :for="`rel-related-${index}`">Related Model</Label>
                            <Input :id="`rel-related-${index}`" v-model="rel.related" placeholder="User, Post" />
                        </div>

                        <div class="flex items-center space-x-2 pt-7">
                            <Checkbox :id="`rel-default-${index}`" v-model="rel.default" />
                            <Label for="rel-default-{{index}}">Default</Label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label :for="`rel-description-${index}`">Description</Label>
                        <Textarea :id="`rel-description-${index}`" v-model="rel.description"
                            placeholder="Describe this relationship" />
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 z-10 p-4 border-t bg-background">
            <div class="flex justify-end gap-4">
                <Button type="button" variant="outline" @click="$emit('cancel')">
                    Cancel
                </Button>
                <Button type="submit">
                    {{ isEditing ? 'Update Model' : 'Create Model' }}
                </Button>
            </div>
        </div>
    </form>
</template>

<script setup lang="ts">
import { PlusIcon, TrashIcon } from 'lucide-vue-next'
import { computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputError from '@/components/InputError.vue'

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    isEditing: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue', 'submit', 'cancel'])

const form = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const attributeTypes = [
    { value: 'bigInteger', label: 'Big Integer' },
    { value: 'integer', label: 'Integer' },
    { value: 'tinyInteger', label: 'Tiny Integer' },
    { value: 'smallInteger', label: 'Small Integer' },
    { value: 'mediumInteger', label: 'Medium Integer' },
    { value: 'unsignedBigInteger', label: 'Unsigned Big Integer' },
    { value: 'unsignedInteger', label: 'Unsigned Integer' },
    { value: 'unsignedMediumInteger', label: 'Unsigned Medium Integer' },
    { value: 'unsignedSmallInteger', label: 'Unsigned Small Integer' },
    { value: 'unsignedTinyInteger', label: 'Unsigned Tiny Integer' },
    { value: 'morphs', label: 'Morphs' },
    { value: 'nullableMorphs', label: 'Nullable Morphs' },
    { value: 'numericMorphs', label: 'Numeric Morphs' },
    { value: 'nullableNumericMorphs', label: 'Nullable Numeric Morphs' },
    { value: 'uuidMorphs', label: 'UUID Morphs' },
    { value: 'nullableUuidMorphs', label: 'Nullable UUID Morphs' },
    { value: 'ulidMorphs', label: 'ULID Morphs' },
    { value: 'nullableUlidMorphs', label: 'Nullable ULID Morphs' },
    { value: 'boolean', label: 'Boolean' },
    { value: 'decimal', label: 'Decimal' },
    { value: 'float', label: 'Float' },
    { value: 'double', label: 'Double' },
    { value: 'string', label: 'String' },
    { value: 'email', label: 'Email' },
    { value: 'text', label: 'Text' },
    { value: 'mediumText', label: 'Medium Text' },
    { value: 'longText', label: 'Long Text' },
    { value: 'binary', label: 'Binary' },
    { value: 'date', label: 'Date' },
    { value: 'datetime', label: 'DateTime' },
    { value: 'dateTime', label: 'DateTime' },
    { value: 'timestamp', label: 'Timestamp' },
    { value: 'timeStamp', label: 'Timestamp' },
    { value: 'time', label: 'Time' },
    { value: 'year', label: 'Year' },
    { value: 'json', label: 'JSON' },
    { value: 'jsonb', label: 'JSONB' },
    { value: 'uuid', label: 'UUID' },
    { value: 'char', label: 'Char' },
    { value: 'enum', label: 'Enum' },
    { value: 'set', label: 'Set' },
    { value: 'geometry', label: 'Geometry' },
    { value: 'point', label: 'Point' },
    { value: 'linestring', label: 'Line String' },
    { value: 'polygon', label: 'Polygon' },
    { value: 'geometryCollection', label: 'Geometry Collection' },
    { value: 'multipoint', label: 'Multi Point' },
    { value: 'multilinestring', label: 'Multi Line String' },
    { value: 'multipolygon', label: 'Multi Polygon' },
    { value: 'ipAddress', label: 'IP Address' },
    { value: 'macAddress', label: 'MAC Address' },
    { value: 'foreignId', label: 'Foreign ID' },
    { value: 'foreignUuid', label: 'Foreign UUID' },
    { value: 'file', label: 'File' },
    { value: 'image', label: 'Image' },
    { value: 'video', label: 'Video' }
];


const relationTypes = [
    { value: 'belongsTo', label: 'Belongs To' },
    { value: 'hasOne', label: 'Has One' },
    { value: 'hasMany', label: 'Has Many' },
    { value: 'belongsToMany', label: 'Belongs To Many' },
    { value: 'morphTo', label: 'Morph To' }
]

const addAttribute = () => {
    form.value.attributes.push({
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
    })
}

const removeAttribute = (index: number) => {
    if (form.value.attributes.length > 1) {
        form.value.attributes.splice(index, 1)
    }
}

const addRelation = () => {
    form.value.relations.push({
        name: '',
        type: 'belongsTo',
        default: false,
        related: '',
        description: ''
    })
}

const removeRelation = (index: number) => {
    form.value.relations.splice(index, 1)
}
</script>
