import type { Lesson } from './lesson';
import type { Quiz } from './quiz';

export type CourseDifficulty = 'beginner' | 'intermediate' | 'advanced';

export interface Course {
    id: number;
    organisation_id: number;
    title: string;
    slug: string;
    summary: string | null;
    description: string | null;
    difficulty: CourseDifficulty;
    difficulty_label: string | null;
    estimated_duration_minutes: number | null;
    instructor_id: number | null;
    published_at: string | null;
    is_published: boolean;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    instructor?: { id: number; name: string } | null;
    lessons_count?: number;
    quizzes_count?: number;
    quizzes?: Quiz[];
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
    lessons?: Lesson[];
}

export interface DifficultyOption {
    value: CourseDifficulty;
    label: string;
}

export interface CourseOption {
    id: number;
    title: string;
}
