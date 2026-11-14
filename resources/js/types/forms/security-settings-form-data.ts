export interface SecuritySettingsFormData {
    two_factor_required: boolean;
    session_timeout_minutes: number;
    max_login_attempts: number;
    password_expiry_days: string;
}