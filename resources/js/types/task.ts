export interface Task {
    id: number;
    organisation_id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    assigned_date: string | null;
    assigned_to: number | null;
    status_id: number | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    assignee?: { id: number; name: string } | null;
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    tags?: { id: number; name: string }[];
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}
