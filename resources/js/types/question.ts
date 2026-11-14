export type QuestionTypeValue =
    'single_choice' | 'multiple_choice' | 'true_false' | 'short_text';

export interface Question {
    id: number;
    questionable_id: number;
    questionable_type: string;
    question: string;
    type: QuestionTypeValue;
    type_label: string;
    explanation: string | null;
    points: number;
    position: number;
    is_required: boolean;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    questionable?: { id: number; label: string } | null;
    creator?: { id: number; name: string } | null;
    updater?: { id: number; name: string } | null;
    deleter?: { id: number; name: string } | null;
    restorer?: { id: number; name: string } | null;
}

export interface QuestionOption {
    id: number;
    prompt: string;
    type: Question['type'];
    type_label: string;
    points: number;
}

export interface QuestionAbilities {
    can_update: boolean;
    can_delete: boolean;
    can_restore: boolean;
    can_force_delete: boolean;
}