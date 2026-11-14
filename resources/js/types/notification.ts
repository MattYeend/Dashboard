export interface Notification {
    id: string;
    type: string | null;
    title: string | null;
    body: string | null;
    action_url: string | null;
    subject_type: string | null;
    subject_id: number | null;
    read_at: string | null;
    created_at: string;
}