export interface ActivityLog {
    id: number;
    action_id: number;
    action_label: string;
    data: Record<string, unknown> | null;
    logged_in_user_id: number | null;
    related_to_user_id: number | null;
    logged_in_user?: { id: number; name: string } | null;
    related_to_user?: { id: number; name: string } | null;
    created_at: string;
}
