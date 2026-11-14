import type { Comment } from './comment';

export interface Ticket {
    id: number;
    organisation_id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    resolved_at: string | null;
    ticket_status_id: number | null;
    ticket_priority_id: number | null;
    assigned_to: number | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    priority?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    assignee?: { id: number; name: string } | null;
    labels?: { id: number; name: string }[];
    comments?: Comment[];
    comments_count?: number;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}