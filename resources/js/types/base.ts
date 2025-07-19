// types/base.ts
export interface DateWithFormats {
    default: string | null;
    short: string | null;
    short_with_time: string | null;
    human: string | null;
}

export interface SubjectBase {
    subject_type: string;
    subject_id: number;
    subject_name: string;
    subject_type_alias?: string;
}

export interface CauserBase {
    causer_type: string | null;
    causer_id: number | null;
    causer_name: string | null;
}
