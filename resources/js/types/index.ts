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

export interface OrderStatus {
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

export interface Order {
    id: number;
    orderable_id: number;
    orderable_type: string;
    orderable_type_key: string;
    orderable_type_label: string | null;
    orderable_name: string | null;
    order_number: string;
    title: string;
    description: string | null;
    notes: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    ordered_at: string | null;
    due_at: string | null;
    completed_at: string | null;
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
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
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

export interface Industry {
    id: number;
    title: string;
    code: string | null;
    description: string | null;
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

export interface Company {
    id: number;
    name: string;
    slug: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    registration_number: string | null;
    vat_number: string | null;
    description: string | null;
    industry_id: number | null;
    account_manager_id: number | null;
    employee_count: number | null;
    founded_year: number | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    industry?: { id: number; title: string } | null;
    account_manager?: { id: number; name: string } | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}

export interface Plan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_per_user_per_month: number;
    is_active: boolean;
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

export interface Address {
    id: number;
    addressable_id: number;
    addressable_type: string;
    addressable_type_key: string;
    addressable_type_label: string | null;
    addressable_name: string | null;
    address_line_one: string;
    address_line_two: string | null;
    town: string | null;
    city: string;
    county: string | null;
    postcode: string | null;
    country: string;
    is_primary: boolean;
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

export interface ApiToken {
    id: number;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    expires_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    parent_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    parent?: { id: number; name: string } | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}

export interface Post {
    id: number;
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
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}

export interface InvoiceStatus {
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
