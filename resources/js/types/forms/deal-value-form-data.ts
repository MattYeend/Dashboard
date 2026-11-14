export interface DealValueFormData {
    value: number;
    currency: string;
    probability: number;
    expected_close_date: string | null;
    closed_at: string | null;
}