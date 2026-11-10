export interface ProductFormData {
    sku: string;
    name: string;
    description: string | null;
    unit: string;
    unit_price: string;
    cost_price: string | null;
    tax_rate: string;
    currency: string;
    is_active: boolean;
}