export * from './auth';
export * from './navigation';
export * from './ui';

export interface TaskStatus {
    id: number;
    title: string;
    description: string | null;
    background_colour: string;
    text_colour: string;
    meta: Record<string, unknown> | null;
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
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    role: 'user' | 'admin' | 'super_admin';
    meta: Record<string, unknown> | null;
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
}

export interface Contact {
    id: number;
    contactable_id: number;
    contactable_type: string;
    contactable_type_key: string;
    contactable_type_label: string | null;
    contactable_name: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    postal_code: string | null;
    country: string | null;
    meta: Record<string, unknown> | null;
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
}

export interface UserOption {
    id: number;
    name: string;
}

export interface Task {
    id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    assigned_date: string | null;
    assigned_to: number | null;
    status_id: number | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    assignee?: { id: number; name: string } | null;
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
}

export interface PermissionsMeta {
    can_create: boolean;
    can_view_any: boolean;
}

export interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}
