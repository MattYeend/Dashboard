export interface EmailTemplateFormData {
    name: string;
    subject: string;
    preheader: string | null;
    is_active: boolean;
    body_html: string;
    body_text: string | null;
}
