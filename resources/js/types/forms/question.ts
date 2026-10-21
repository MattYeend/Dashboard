import type { QuestionTypeValue } from '@/types';

export interface QuestionFormData {
    questionable_type: string;
    questionable_id: number | null;
    question: string;
    type: QuestionTypeValue;
    explanation: string | null;
    points: number;
    position: number | null;
    is_required: boolean;
}
