export interface Answer {
    id: number;
    organisation_id: number;
    question_id: number;
    answer: string;
    /** Only present for users who can view correct answers. */
    is_correct?: boolean;
    /** Only present for users who can view correct answers. */
    feedback?: string | null;
    position: number;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    question?: { id: number; question: string } | null;
    creator?: { id: number; name: string } | null;
    updater?: { id: number; name: string } | null;
    deleter?: { id: number; name: string } | null;
    restorer?: { id: number; name: string } | null;
}

export interface AnswerAbilities {
    can_update: boolean;
    can_delete: boolean;
    can_restore: boolean;
    can_force_delete: boolean;
}