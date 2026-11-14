export interface InteractionLog {
    id: number;
    organisation_id: number;
    interactable_id: number;
    interactable_type: string;
    interactable_type_key: string;
    interactable_type_label: string | null;
    type: 'call' | 'email';
    subject: string;
    outcome: string | null;
    notes: string | null;
    occurred_at: string;
    contact_id: number | null;
    contact?: { id: number; name: string } | null;
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
    can_update?: boolean;
    can_delete?: boolean;
}
