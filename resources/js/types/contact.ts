export interface Contact {
    id: number;
    organisation_id: number;
    contactable_id: number;
    contactable_type: string;
    contactable_type_key: string;
    contactable_type_label: string | null;
    contactable_name: string | null;
    name: string | null;
    phone: string | null;
    email: string | null;
    meta: Record<string, unknown> | null;
    tags?: { id: number; name: string }[];
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
}