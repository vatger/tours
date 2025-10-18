import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Tour {
    id: number;
    name: string;
    description: string;
    img_url?: string;
    link?: string;
    begins_at: string;
    ends_at: string;
    aircraft: string|null;
    flight_rules: string;
    require_order: boolean;
    legs?: Leg[];
}

export interface Leg {
    id: number;
    tour_id: number;
    departure_icao: string;
    arrival_icao: string;
    status: Status|null
}

export interface Status {
    id: number;
    user_id: number;
    tour_leg_id: number;
    fight_data_id: number|null;
    completed_at:string|null
}
