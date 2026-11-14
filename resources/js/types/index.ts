import type { ActivityLog } from './activity-log';

export * from './activity';
export * from './activity-log';
export * from './address';
export * from './answer';
export * from './api-token';
export * from './attachment';
export * from './auth';
export * from './category';
export * from './comment';
export * from './company';
export * from './contact';
export * from './course';
export * from './deal';
export * from './deal-status';
export * from './import';
export * from './industry';
export * from './interaction-log';
export * from './invoice';
export * from './invoice-item';
export * from './invoice-status';
export * from './label';
export * from './lesson';
export * from './navigation';
export * from './notification';
export * from './order';
export * from './order-status';
export * from './organisation';
export * from './organisation-data-privacy-permissions';
export * from './organisation-membership';
export * from './payment-status';
export * from './permission';
export * from './pipeline';
export * from './pipeline-stage';
export * from './pipeline-status';
export * from './plan';
export * from './platform-reporting-metrics';
export * from './post';
export * from './product';
export * from './question';
export * from './quiz';
export * from './quote';
export * from './quote-status';
export * from './registration-interest';
export * from './report';
export * from './subscription';
export * from './tag';
export * from './task';
export * from './task-status';
export * from './ticket';
export * from './ticket-priority';
export * from './ticket-status';
export * from './ui';
export * from './user';

export interface UserOption {
    id: number;
    name: string;
}

export interface UserSession {
    ip_address: string | null;
    user_agent: string | null;
    last_active_at: string;
    is_current: boolean;
}

export interface PermissionsMeta {
    can_create: boolean;
    can_view_any: boolean;
    can_export?: boolean;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
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

export interface SearchResultItem {
    id: number;
    label: string;
    url: string;
}

export interface SearchResults {
    users: SearchResultItem[];
    companies: SearchResultItem[];
    contacts: SearchResultItem[];
    orders: SearchResultItem[];
    deals: SearchResultItem[];
}

export interface InteractionLogPermissionsMeta {
    can_create: boolean;
}

export interface DashboardPermissionsMeta {
    can_view_statistics: boolean;
    can_view_charts: boolean;
    can_export: boolean;
}

export interface DashboardChart {
    key: string;
    label: string;
    data: Array<{
        label: string;
        value: number;
    }>;
}

export interface CalendarEvent {
    id: number;
    title: string;
    start: string;
    background_colour: string | null;
    text_colour: string | null;
    show_url: string;
}

export interface Setting {
    id: number;
    site_name: string;
    support_email: string;
    timezone: string;
    date_format: string;
    maintenance_mode: boolean;
    allow_registrations: boolean;
    default_pagination: number;
    default_locale: string;
    two_factor_required: boolean;
    session_timeout_minutes: number;
    max_login_attempts: number;
    password_expiry_days: number | null;
    updater?: { id: number; name: string };
    updated_at: string;
}

export interface SettingsPermissions {
    can_view_general: boolean;
    can_edit_general: boolean;
    can_view_system: boolean;
    can_edit_system: boolean;
    can_view_security: boolean;
    can_edit_security: boolean;
}

export interface OrganisationMembershipPermissionsMeta {
    can_invite: boolean;
    can_remove: boolean;
}

export interface AuditLogEntry extends ActivityLog {
    is_sealed: boolean;
    sequence: number | null;
    hash: string | null;
    previous_hash: string | null;
}

export interface AuditTrailPermissionsMeta {
    can_view_any: boolean;
    can_export: boolean;
}

export interface QuestionPermissionsMeta extends PermissionsMeta {
    can_export: boolean;
    can_import: boolean;
    can_restore: boolean;
}

export interface SelectOption {
    value: string;
    label: string;
}

export interface AnswerPermissionsMeta extends PermissionsMeta {
    can_export: boolean;
    can_import: boolean;
    can_delete: boolean;
    can_restore: boolean;
    can_view_correct: boolean;
}

export interface NotificationBroadcast {
    id: number;
    title: string;
    body: string;
    audience_type: 'all' | 'role' | 'users';
    audience_ids: string[] | null;
    sent_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
    sender?: { name: string };
}
