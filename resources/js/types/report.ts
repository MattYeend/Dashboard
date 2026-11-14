export interface Report {
    id: number;
    organisation_id: number;
    title: string;
    description: string | null;
    type: string;
    type_label: string | null;
    format: 'pdf' | 'csv' | 'xlsx';
    filters: Record<string, unknown> | null;
    is_scheduled: boolean;
    schedule_frequency: 'daily' | 'weekly' | 'monthly' | null;
    schedule_time: string | null;
    recipients: string[] | null;
    last_run_at: string | null;
    next_run_at: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
    can_update?: boolean;
    can_delete?: boolean;
    can_schedule?: boolean;
}

export interface ReportType {
    value: string;
    label: string;
}

export interface ReportPermissionsMeta {
    can_create: boolean;
    can_view_any: boolean;
    can_export: boolean;
    can_schedule: boolean;
}