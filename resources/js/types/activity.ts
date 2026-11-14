export interface Activity {
    id: number;
    organisation_id: number;
    activityable_id: number;
    activityable_type: string;
    activityable_type_key: string;
    activityable_type_label: string | null;
    activityable_name: string | null;
    type:
        | 'note'
        | 'status_change'
        | 'task_created'
        | 'call_logged'
        | 'email_logged';
    type_label: string;
    description: string | null;
    meta: Record<string, unknown> | null;
    occurred_at: string;
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
}