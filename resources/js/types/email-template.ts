export interface EmailTemplate {
    id: number;
    name: string;
    subject: string;
    preheader: string | null;
    body_html: string;
    body_text: string | null;
    is_active: boolean;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    creator?: { name: string } | null;
    updater?: { name: string } | null;
    deleter?: { name: string } | null;
    restorer?: { name: string } | null;
}
