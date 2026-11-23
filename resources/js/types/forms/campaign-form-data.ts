export interface CampaignFormData {
    name: string;
    email_template_id: string;
    campaign_segment_id: string;
    owner_id: string;
    subject: string | null;
    from_name: string | null;
    reply_to: string | null;
    scheduled_at: string | null;
}
