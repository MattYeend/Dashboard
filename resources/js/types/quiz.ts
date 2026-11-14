import type { Answer } from './answer';
import type { Question } from './question';

export interface QuizQuestion {
    id: number;
    type: Question['type'];
    type_label: string;
    prompt: string;
    /** Only present for users who can edit quizzes. */
    explanation?: string | null;
    /** Only present for users who can edit quizzes. */
    default_points?: number;
    /** Only present for users who can edit quizzes. The per quiz override, or null for the default. */
    points?: number | null;
    effective_points: number;
    position: number;
    answers: Answer[];
}

export interface Quiz {
    id: number;
    organisation_id: number;
    course_id: number;
    lesson_id: number | null;
    title: string;
    slug: string;
    description: string | null;
    pass_mark_percentage: number;
    time_limit_minutes: number | null;
    max_attempts: number | null;
    shuffle_questions: boolean;
    show_correct_answers: boolean;
    published_at: string | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    course?: { id: number; title: string } | null;
    lesson?: { id: number; title: string } | null;
    questions?: QuizQuestion[];
    questions_count?: number | null;
    published_at_iso: string | null;
    course_title?: string;
    lesson_title?: string;
    creator?: { id: number; name: string } | null;
    updater?: { id: number; name: string } | null;
    deleter?: { id: number; name: string } | null;
    restorer?: { id: number; name: string } | null;
}