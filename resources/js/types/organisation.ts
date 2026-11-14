export interface OrganisationSettings {
    logo_attachment_id: number | null;
    accent_colour: string | null;
    default_timezone: string | null;
    default_locale: string | null;
    notification_preferences: {
        email_notifications: boolean;
        weekly_digest: boolean;
    } | null;
}

export interface Organisation {
    id: number;
    name: string;
    slug: string;
    meta: Record<string, unknown> | null;
    settings: OrganisationSettings | null;
    members_count?: number;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}
