export * from './auth';
export * from './import';
export * from './navigation';
export * from './product';
export * from './quote';
export * from './quote-status';
export * from './payment-status';
export * from './ui';
export * from './deal-status';
export * from './invoice-status';
export * from './order-status';
export * from './pipeline-status';
export * from './task-status';
export * from './ticket-status';
export * from './address';
export * from './company';
export * from './contact';
export * from './industry';
export * from './task';
export * from './user';
export * from './invoice';
export * from './invoice-item';
export * from './order';
export * from './plan';
export * from './subscription';
export * from './deal';
export * from './pipeline';
export * from './pipeline-stage';
export * from './activity';
export * from './attachment';
export * from './category';
export * from './comment';
export * from './interaction-log';
export * from './label';
export * from './notification';
export * from './post';
export * from './tag';
export * from './ticket';
export * from './ticket-priority';
export * from './activity-log';
export * from './api-token';
export * from './organisation';
export * from './organisation-data-privacy-permissions';
export * from './organisation-membership';
export * from './permission';
export * from './platform-reporting-metrics';
export * from './registration-interest';
export * from './report';
import type { ActivityLog } from './activity-log';

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

export interface CourseOption {
    id: number;
    title: string;
}

export interface LessonNeighbour {
    id: number;
    title: string;
}

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

export interface QuestionOption {
    id: number;
    prompt: string;
    type: Question['type'];
    type_label: string;
    points: number;
}

export interface LessonOption {
    id: number;
    title: string;
}

export type QuestionTypeValue =
    'single_choice' | 'multiple_choice' | 'true_false' | 'short_text';

export interface Question {
    id: number;
    questionable_id: number;
    questionable_type: string;
    question: string;
    type: QuestionTypeValue;
    type_label: string;
    explanation: string | null;
    points: number;
    position: number;
    is_required: boolean;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    questionable?: { id: number; label: string } | null;
    creator?: { id: number; name: string } | null;
    updater?: { id: number; name: string } | null;
    deleter?: { id: number; name: string } | null;
    restorer?: { id: number; name: string } | null;
}

export interface QuestionPermissionsMeta extends PermissionsMeta {
    can_export: boolean;
    can_import: boolean;
    can_restore: boolean;
}

export interface QuestionAbilities {
    can_update: boolean;
    can_delete: boolean;
    can_restore: boolean;
    can_force_delete: boolean;
}

export interface SelectOption {
    value: string;
    label: string;
}

export interface Answer {
    id: number;
    organisation_id: number;
    question_id: number;
    answer: string;
    /** Only present for users who can view correct answers. */
    is_correct?: boolean;
    /** Only present for users who can view correct answers. */
    feedback?: string | null;
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
    question?: { id: number; question: string } | null;
    creator?: { id: number; name: string } | null;
    updater?: { id: number; name: string } | null;
    deleter?: { id: number; name: string } | null;
    restorer?: { id: number; name: string } | null;
}

export interface AnswerPermissionsMeta extends PermissionsMeta {
    can_export: boolean;
    can_import: boolean;
    can_delete: boolean;
    can_restore: boolean;
    can_view_correct: boolean;
}

export interface AnswerAbilities {
    can_update: boolean;
    can_delete: boolean;
    can_restore: boolean;
    can_force_delete: boolean;
}
