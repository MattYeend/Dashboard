export * from './auth';
export * from './navigation';
export * from './ui';

export interface TicketStatus {
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
    roles: string[];
    locale: string;
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
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
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

export interface Comment {
    id: number;
    commentable_id: number;
    commentable_type: string;
    commentable_type_key: string | null;
    commentable_type_label: string | null;
    commentable_name: string | null;
    content: string;
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

export interface Tag {
    id: number;
    name: string;
    slug: string;
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

export interface Invoice {
    id: number;
    invoice_number: string;
    company_id: number | null;
    order_id: number | null;
    status_id: number | null;
    issue_date: string | null;
    due_date: string | null;
    sent_at: string | null;
    paid_at: string | null;
    subtotal: number;
    tax_total: number;
    total: number;
    currency: string;
    notes: string | null;
    items?: InvoiceItem[];
    items_count?: number;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    company?: { id: number; name: string } | null;
    contact?: {
        id: number;
        phone: string | null;
        email: string | null;
        address: string | null;
        city: string | null;
        postal_code: string | null;
        country: string | null;
    } | null;
    order?: { id: number } | null;
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

export interface InvoiceItem {
    id: number;
    invoice_id: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: string;
    total: number;
    position: number;
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

export interface RegistrationInterest {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company: string | null;
    message: string | null;
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

export interface PipelineStatus {
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

export interface Pipeline {
    id: number;
    title: string;
    description: string | null;
    is_default: boolean;
    status_id: number | null;
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
    assignee?: { id: number; name: string } | null;
    stages?: PipelineStage[];
    stages_count?: number;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}

export interface PipelineStage {
    id: number;
    pipeline_id: number;
    title: string;
    description: string | null;
    position: number;
    background_colour: string;
    text_colour: string;
    is_won: boolean;
    is_lost: boolean;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    pipeline?: { id: number; title: string } | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}

export interface DealStatus {
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

export interface Deal {
    id: number;
    title: string;
    description: string | null;
    pipeline_id: number | null;
    stage_id: number | null;
    status_id: number | null;
    company_id: number | null;
    invoice_id: number | null;
    value: number;
    currency: string;
    probability: number;
    expected_close_date: string | null;
    closed_at: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    pipeline?: { id: number; title: string } | null;
    stage?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    company?: { id: number; name: string } | null;
    invoice?: { id: number; invoice_number: string } | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}

export interface TicketPriority {
    id: number;
    title: string;
    level: number;
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

export interface Label {
    id: number;
    name: string;
    slug: string;
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

export interface Ticket {
    id: number;
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

export interface Activity {
    id: number;
    activityable_id: number;
    activityable_type: string;
    activityable_type_key: string;
    activityable_type_label: string | null;
    activityable_name: string | null;
    type:
        | 'note'
        | 'status_change'
        | 'task_created'
        | 'call_logged'
        | 'email_logged';
    type_label: string;
    description: string | null;
    meta: Record<string, unknown> | null;
    occurred_at: string;
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

export interface InteractionLog {
    id: number;
    interactable_id: number;
    interactable_type: string;
    interactable_type_key: string;
    interactable_type_label: string | null;
    type: 'call' | 'email';
    subject: string;
    outcome: string | null;
    notes: string | null;
    occurred_at: string;
    contact_id: number | null;
    contact?: { id: number; name: string } | null;
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
    can_update?: boolean;
    can_delete?: boolean;
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

export interface Notification {
    id: string;
    type: string | null;
    title: string | null;
    body: string | null;
    action_url: string | null;
    subject_type: string | null;
    subject_id: number | null;
    read_at: string | null;
    created_at: string;
}

export interface DashboardStats {
    tasks: {
        completed: number;
        outstanding: number;
    };
    companies: {
        total: number;
        created_this_month: number;
    };
    deals: {
        total: number;
        won: number;
        lost: number;
    };
    pipelines: {
        total: number;
        won: number;
        lost: number;
    };
    orders: {
        total: number;
        completed: number;
        outstanding: number;
    };
    invoices: {
        total: number;
        paid: number;
        outstanding: number;
    };
    posts: DashboardLatestPost[];
}

export interface DashboardLatestPost {
    id: number;
    title: string;
    created_at: string;
    creator?: { name: string };
}

export interface DashboardWidget {
    id?: number;
    key: string;
    label: string;
    description?: string | null;
    group: string;
    type: 'builtin' | 'custom';
    position: number;
    is_visible: boolean;
    value?: number;
}

export interface DashboardMetric {
    key: string;
    label: string;
    model: string;
}

export interface SystemInfo {
    php_version: string;
    laravel_version: string;
    environment: string;
    debug_mode: boolean;
    maintenance_mode: boolean;
    database_connection: 'connected' | 'disconnected';
    cache_driver: string;
    queue_driver: string;
    disk_free_space: string;
    disk_total_space: string;
    server_time: string;
}

export interface Backup {
    filename: string;
    disk: string;
    size: number;
    size_human: string;
    date: string;
}

export interface ActivityPermissionsMeta {
    can_create: boolean;
    can_export: boolean;
}

export interface InteractionLogPermissionsMeta {
    can_create: boolean;
}
