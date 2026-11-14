export interface OrganisationMembership {
    id: number;
    organisation_id: number;
    user_id: number;
    status: 'invited' | 'active';
    invited_role: string | null;
    invited_at: string | null;
    joined_at: string | null;
    user?: { id: number; name: string; email: string };
}
