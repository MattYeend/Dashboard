export interface NotificationBroadcastFormData {
    title: string;
    body: string;
    audience_type: 'all' | 'role' | 'users';
    audience_ids: string;
}
