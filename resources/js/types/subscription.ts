export interface Subscription {
    id: number;
    organisation_id: number;
    plan_id: number | null;
    stripe_id: string;
    stripe_status: string;
    payment_status: 'current' | 'past_due' | 'unpaid';
    quantity: number | null;
    trial_ends_at: string | null;
    ends_at: string | null;
    created_at: string;
    updated_at: string;
    plan?: { id: number; name: string } | null;
}