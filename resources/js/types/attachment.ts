export interface Attachment {
    id: number;
    organisation_id: number;
    original_filename: string;
    mime_type: string;
    size_bytes: number;
    size_human: string;
    download_url: string;
    created_at: string;
    deleted_at: string | null;
    creator?: { id: number; name: string } | null;
    deleter?: { id: number; name: string } | null;
}