export interface PlanFormData {
    name: string;
    slug: string | null;
    description: string | null;
    price_per_user_per_month: number;
    is_active: boolean;
}