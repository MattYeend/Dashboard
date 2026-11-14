export interface AnswerFormData {
    question_id: number | null;
    answer: string;
    is_correct: boolean;
    feedback: string | null;
    position: number | null;
}
