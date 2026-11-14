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