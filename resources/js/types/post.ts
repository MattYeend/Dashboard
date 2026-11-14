import type { Comment } from './comment';

export interface Post {
    id: number;
    organisation_id: number;
    title: string;
    description: string;
    image: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    categories?: { id: number; name: string }[];
    tags?: { id: number; name: string }[];
    likes_count?: number;
    liked_by_user?: boolean;
    comments?: Comment[];
    comments_count?: number;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}