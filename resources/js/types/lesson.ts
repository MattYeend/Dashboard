export interface Lesson {
    id: number;
    organisation_id: number;
    course_id: number;
    title: string;
    slug: string;
    summary: string | null;
    content: string | null;
    video_url: string | null;
    embed_url: string | null;
    position: number;
    duration_minutes: number | null;
    is_preview: boolean;
    published_at: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    course?: { id: number; title: string };
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}

export interface LessonNeighbour {
    id: number;
    title: string;
}

export interface LessonOption {
    id: number;
    title: string;
}