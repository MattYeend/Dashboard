export interface CommentMention {
    id: number;
    name: string;
}

export interface Comment {
    id: number;
    organisation_id: number;
    commentable_id: number;
    commentable_type: string;
    commentable_type_key: string | null;
    commentable_type_label: string | null;
    commentable_name: string | null;
    content: string;
    mentions?: CommentMention[];
    meta: Record<string, unknown> | null;
    likes_count?: number;
    liked_by_user?: boolean;
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
    deleter?: { name: string };
    restorer?: { name: string };
    can_update?: boolean;
    can_delete?: boolean;
}