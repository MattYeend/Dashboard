export interface QuoteItemFormData {
    product_id: number | null;
    name: string;
    description: string | null;
    unit: string;
    quantity: string;
    unit_price: string;
    discount_percent: string;
    tax_rate: string;
}

export interface ProductOption {
    id: number;
    sku: string;
    name: string;
    description: string | null;
    unit: string;
    unit_price: string;
    tax_rate: string;
}
