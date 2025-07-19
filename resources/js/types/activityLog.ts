// types/activityLog.ts
import { CauserBase, DateWithFormats, SubjectBase } from './base';

export interface ActivityLog extends SubjectBase, CauserBase {
    id: number;
    event: string;
    description: string;
    properties: {
        old: Record<string, any>;
        attributes: Record<string, any>;
    };
    created_at: DateWithFormats;
    updated_at: DateWithFormats;
}
