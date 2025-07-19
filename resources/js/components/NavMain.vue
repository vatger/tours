<script setup lang="ts">
import {
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
    useSidebar,
    SidebarGroup,
    SidebarGroupLabel,
} from '@/components/ui/sidebar'
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover'
import { Link, usePage } from '@inertiajs/vue3'
import { ChevronRight } from 'lucide-vue-next'
import type { NavItem } from '@/types'
import { computed, ref } from 'vue'

const { items, label, level = 0 } = defineProps<{ items: NavItem[], label?: string, level?: number }>();
const page = usePage()
const sidebar = useSidebar()
const collapsed = computed(() => sidebar.state.value === 'collapsed')

function isActive(item: NavItem): boolean {
    if (item.href === page.url) return true
    if (item.children) return item.children.some(isActive)
    return false
}
const openMenus = ref<{ [key: string]: boolean }>({})
function toggleMenu(key: string) {
    openMenus.value[key] = !openMenus.value[key]
}
function isOpen(item: NavItem, key: string) {
    return openMenus.value[key] || isActive(item)
}
</script>

<template>
    <SidebarGroup v-if="level === 0" class="px-2 py-0">
        <SidebarGroupLabel v-if="label">{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- ITEM SEM FILHOS -->
                <SidebarMenuItem v-if="!item.children">
                    <component :is="collapsed ? Tooltip : 'span'">
                        <TooltipTrigger v-if="collapsed" as-child>
                            <SidebarMenuButton as-child :is-active="isActive(item)">
                                <Link :href="item.href ?? ''">
                                <component v-if="item.icon" :is="item.icon" class="mr-2" />
                                <span v-if="!collapsed">{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </TooltipTrigger>
                        <TooltipContent v-if="collapsed">{{ item.title }}</TooltipContent>
                        <SidebarMenuButton v-else as-child :is-active="isActive(item)">
                            <Link :href="item.href ?? ''">
                            <component v-if="item.icon" :is="item.icon" class="mr-2" />
                            <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </component>
                </SidebarMenuItem>

                <!-- ITEM COM FILHOS -->
                <SidebarMenuItem v-else>
                    <!-- COLAPSADO: Mostra popover/flyout -->
                    <Popover v-if="collapsed">
                        <PopoverTrigger as-child>
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <SidebarMenuButton :is-active="isActive(item)">
                                        <component v-if="item.icon" :is="item.icon" class="mr-2" />
                                    </SidebarMenuButton>
                                </TooltipTrigger>
                                <TooltipContent>{{ item.title }}</TooltipContent>
                            </Tooltip>
                        </PopoverTrigger>
                        <PopoverContent side="right" class="p-0 w-56">
                            <SidebarMenuSub>
                                <template v-for="child in item.children" :key="child.title">
                                    <SidebarMenuSubItem v-if="!child.children">
                                        <SidebarMenuSubButton as-child :is-active="isActive(child)">
                                            <Link :href="child.href ?? ''">
                                            <component v-if="child.icon" :is="child.icon" class="mr-2" />
                                            <span>{{ child.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                    <SidebarMenuSubItem v-else>
                                        <Popover>
                                            <PopoverTrigger as-child>
                                                <SidebarMenuSubButton :is-active="isActive(child)">
                                                    <component v-if="child.icon" :is="child.icon" class="mr-2" />
                                                    <span>{{ child.title }}</span>
                                                    <ChevronRight class="ml-auto" />
                                                </SidebarMenuSubButton>
                                            </PopoverTrigger>
                                            <PopoverContent side="right" class="p-0 w-56">
                                                <SidebarMenuSub>
                                                    <template v-for="grand in child.children" :key="grand.title">
                                                        <SidebarMenuSubItem v-if="!grand.children">
                                                            <SidebarMenuSubButton as-child :is-active="isActive(grand)">
                                                                <Link :href="grand.href ?? ''">
                                                                <component v-if="grand.icon" :is="grand.icon"
                                                                    class="mr-2" />
                                                                <span>{{ grand.title }}</span>
                                                                </Link>
                                                            </SidebarMenuSubButton>
                                                        </SidebarMenuSubItem>
                                                        <SidebarMenuSubItem v-else>
                                                            <Popover>
                                                                <PopoverTrigger as-child>
                                                                    <SidebarMenuSubButton :is-active="isActive(grand)">
                                                                        <component v-if="grand.icon" :is="grand.icon"
                                                                            class="mr-2" />
                                                                        <span>{{ grand.title }}</span>
                                                                        <ChevronRight class="ml-auto" />
                                                                    </SidebarMenuSubButton>
                                                                </PopoverTrigger>
                                                                <PopoverContent side="right" class="p-0 w-56">
                                                                    <SidebarMenuSub>
                                                                        <SidebarMenuSubItem
                                                                            v-for="great in grand.children"
                                                                            :key="great.title">
                                                                            <SidebarMenuSubButton as-child
                                                                                :is-active="isActive(great)">
                                                                                <Link :href="great.href ?? ''">
                                                                                <component v-if="great.icon"
                                                                                    :is="great.icon" class="mr-2" />
                                                                                <span>{{ great.title }}</span>
                                                                                </Link>
                                                                            </SidebarMenuSubButton>
                                                                        </SidebarMenuSubItem>
                                                                    </SidebarMenuSub>
                                                                </PopoverContent>
                                                            </Popover>
                                                        </SidebarMenuSubItem>
                                                    </template>
                                                </SidebarMenuSub>
                                            </PopoverContent>
                                        </Popover>
                                    </SidebarMenuSubItem>
                                </template>
                            </SidebarMenuSub>
                        </PopoverContent>
                    </Popover>
                    <!-- EXPANDIDO: Mostra inline com seta e expansão reativa -->
                    <template v-else>
                        <SidebarMenuButton :is-active="isActive(item)" class="flex items-center justify-between w-full"
                            @click="toggleMenu(item.title)">
                            <span class="flex items-center gap-2">
                                <component v-if="item.icon" :is="item.icon" class="mr-2" />
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <span class="whitespace-nowrap overflow-hidden text-ellipsis block">
                                            {{ item.title }}
                                        </span>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        {{ item.title }}
                                    </TooltipContent>
                                </Tooltip>
                            </span>
                            <span class="ml-auto transition-transform duration-200"
                                :class="isOpen(item, item.title) ? 'rotate-90' : ''">
                                <ChevronRight />
                            </span>
                        </SidebarMenuButton>
                        <SidebarMenuSub v-if="isOpen(item, item.title)">
                            <template v-for="child in item.children" :key="child.title">
                                <SidebarMenuSubItem v-if="!child.children">
                                    <SidebarMenuSubButton as-child :is-active="isActive(child)">
                                        <Link :href="child.href ?? ''">
                                        <component v-if="child.icon" :is="child.icon" class="mr-2" />
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <span class="whitespace-nowrap overflow-hidden text-ellipsis block">
                                                    {{ child.title }}
                                                </span>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                {{ child.title }}
                                            </TooltipContent>
                                        </Tooltip>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                                <SidebarMenuSubItem v-else>
                                    <SidebarMenuSubButton :is-active="isActive(child)"
                                        class="flex items-center justify-between w-full"
                                        @click.stop="toggleMenu(item.title + '-' + child.title)">
                                        <span class="flex items-center gap-2">
                                            <component v-if="child.icon" :is="child.icon" class="mr-2" />
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <span class="whitespace-nowrap overflow-hidden text-ellipsis block">
                                                        {{ child.title }}
                                                    </span>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{ child.title }}
                                                </TooltipContent>
                                            </Tooltip>
                                        </span>
                                        <span class="ml-auto transition-transform duration-200"
                                            :class="isOpen(child, item.title + '-' + child.title) ? 'rotate-90' : ''">
                                            <ChevronRight />
                                        </span>
                                    </SidebarMenuSubButton>
                                    <SidebarMenuSub v-if="isOpen(child, item.title + '-' + child.title)">
                                        <template v-for="grand in child.children" :key="grand.title">
                                            <SidebarMenuSubItem v-if="!grand.children">
                                                <SidebarMenuSubButton as-child :is-active="isActive(grand)">
                                                    <Link :href="grand.href ?? ''">
                                                    <component v-if="grand.icon" :is="grand.icon" class="mr-2" />
                                                    <Tooltip>
                                                        <TooltipTrigger as-child>
                                                            <span
                                                                class="whitespace-nowrap overflow-hidden text-ellipsis block">
                                                                {{ grand.title }}
                                                            </span>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            {{ grand.title }}
                                                        </TooltipContent>
                                                    </Tooltip>
                                                    </Link>
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                            <SidebarMenuSubItem v-else>
                                                <SidebarMenuSubButton :is-active="isActive(grand)"
                                                    class="flex items-center justify-between w-full"
                                                    @click.stop="toggleMenu(item.title + '-' + child.title + '-' + grand.title)">
                                                    <span class="flex items-center gap-2">
                                                        <component v-if="grand.icon" :is="grand.icon" class="mr-2" />
                                                        <Tooltip>
                                                            <TooltipTrigger as-child>
                                                                <span
                                                                    class="whitespace-nowrap overflow-hidden text-ellipsis block">
                                                                    {{ grand.title }}
                                                                </span>
                                                            </TooltipTrigger>
                                                            <TooltipContent>
                                                                {{ grand.title }}
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </span>
                                                    <span class="ml-auto transition-transform duration-200"
                                                        :class="isOpen(grand, item.title + '-' + child.title + '-' + grand.title) ? 'rotate-90' : ''">
                                                        <ChevronRight />
                                                    </span>
                                                </SidebarMenuSubButton>
                                                <SidebarMenuSub
                                                    v-if="isOpen(grand, item.title + '-' + child.title + '-' + grand.title)">
                                                    <SidebarMenuSubItem v-for="great in grand.children"
                                                        :key="great.title">
                                                        <SidebarMenuSubButton as-child :is-active="isActive(great)">
                                                            <Link :href="great.href ?? ''">
                                                            <component v-if="great.icon" :is="great.icon"
                                                                class="mr-2" />
                                                            <Tooltip>
                                                                <TooltipTrigger as-child>
                                                                    <span
                                                                        class="whitespace-nowrap overflow-hidden text-ellipsis block">
                                                                        {{ great.title }}
                                                                    </span>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    {{ great.title }}
                                                                </TooltipContent>
                                                            </Tooltip>
                                                            </Link>
                                                        </SidebarMenuSubButton>
                                                    </SidebarMenuSubItem>
                                                </SidebarMenuSub>
                                            </SidebarMenuSubItem>
                                        </template>
                                    </SidebarMenuSub>
                                </SidebarMenuSubItem>
                            </template>
                        </SidebarMenuSub>
                    </template>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
