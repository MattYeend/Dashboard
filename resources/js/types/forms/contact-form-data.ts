export interface ContactFormData {
    name: string;
    phone: string;
    email: string;
    contactable_type: string;
    contactable_id: number | null;
    tag_ids: number[];
}