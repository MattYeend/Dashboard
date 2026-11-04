import type { User } from './index';

export type SharedAuthUser = Pick<
    User,
    'id' | 'name' | 'email' | 'email_verified_at' | 'role'
>;

export interface Organisation {
    id: number;
    name: string;
}

export interface FlashMessages {
    plainTextToken?: string | null;
    success?: string | null;
    error?: string | null;
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: {
                user: SharedAuthUser;
            };
            currentOrganisation: Organisation | null;
            sidebarOpen: boolean;
            flash: FlashMessages;
        };
    }
}
