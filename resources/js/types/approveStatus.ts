// types/activityLog.ts
import { CauserBase, DateWithFormats, SubjectBase } from './base';

export interface ApproveStatus extends SubjectBase, CauserBase {
    id: number;
    approved_status: string;
    approved_status_text: string;
    motive: string;

    created_at: DateWithFormats;
}
