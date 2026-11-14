export interface Plan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_per_user_per_month: number;
    is_active: boolean;
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