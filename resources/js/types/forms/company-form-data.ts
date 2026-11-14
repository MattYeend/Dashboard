export interface CompanyFormData {
    name: string;
    slug: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    registration_number: string | null;
    vat_number: string | null;
    description: string | null;
    industry_id: number | null;
    account_manager_id: number | null;
    employee_count: number | null;
    founded_year: number | null;
    tag_ids: number[];
}