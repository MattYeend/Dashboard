export interface Product {
    id: number;
    sku: string;
    name: string;
    description: string | null;
    unit: string;
    unit_price: string;
    cost_price: string | null;
    tax_rate: string;
    currency: string;
    formatted_unit_price: string;
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