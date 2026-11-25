export interface EmailSuppression {
    id: number;
    email: string;
    reason: string;
    campaign_id: number | null;
    contact_id: number | null;
    suppressed_at: string | null;
    notes: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    campaign?: { id: number; name: string } | null;
    contact?: { id: number; title: string } | null;
    creator?: { name: string } | null;
    updater?: { name: string } | null;
    deleter?: { name: string } | null;
    restorer?: { name: string } | null;
}
