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
    isLive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    liveFlight: LiveFlight | null;
    quickStatsDown: boolean;
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

export interface LiveFlight {
    id: number;
    account_id: number;
    callsign: string;
    departure_airport: string;
    arrival_airport: string;
    alternative_airport?: string | null;
    aircraft: string;
    flight_type: string;
    cruise_altitude?: string | null;
    cruise_tas?: string | null;
    route?: string | null;
    current_latitude?: number | null;
    current_longitude?: number | null;
    current_altitude?: number | null;
    current_groundspeed?: number | null;
    current_heading?: number | null;
    connected_at: string | null;
    departed_at: string | null;
    arrived_at: string | null;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Tour {
    id: number;
    name: string;
    description: string;
    img_url?: string;
    badge_img_url?: string | null;
    link?: string;
    begins_at: string;
    ends_at: string;
    aircraft: string | null;
    flight_rules: string | null;
    require_order: boolean;
    forum_badge_id?: number | null;
    legs?: Leg[];
    status?: TourUser;
}

export interface TourUser {
    id: number;
    user_id: number;
    tour_id: number;
    completed: boolean;
    badge_given: boolean;
}

export interface Leg {
    id: number;
    tour_id: number;
    departure_icao: string;
    arrival_icao: string;
    status: Status | null;
    completed_users_count?: number;
}

export interface AirportCoordinate {
    icao: string;
    name: string;
    latitude: number;
    longitude: number;
}

export interface Status {
    id: number;
    user_id: number;
    tour_leg_id: number;
    fight_data_id: number | null;
    statsim_flight_id: number | null;
    completed_at: string | null;
}
