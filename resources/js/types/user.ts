// types/user.ts
import { ActivityLog } from './activityLog';
import { DateWithFormats } from './base';
import { Role } from './role';

export interface User {
    id: number;
    name: string;
    email: string;
    username: string;
    nickname?: string;
    pin_code?: string;
    locale?: string;
    avatar?: string;
    avatar_url?: string;
    //    email_verified_at: DateWithFormats;
    created_at?: DateWithFormats;
    updated_at?: DateWithFormats;
    deleted_at?: DateWithFormats | null;
    registeredBy?: User | null;
    activities?: ActivityLog[];
    actions?: ActivityLog[];
    roles?: Role[];
}
