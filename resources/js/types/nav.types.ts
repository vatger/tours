import { LucideIcon } from 'lucide-react'

export interface NavItemType {
    title: string
    url: string
    icon?: LucideIcon|null
    isActive?: boolean
    items?: { title: string; url: string }[]
}