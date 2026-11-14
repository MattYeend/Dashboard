export interface TicketFormData {
    title: string;
    description: string | null;
    ticket_status_id: number | null;
    ticket_priority_id: number | null;
    assigned_to: number | null;
    due_date: string | null;
    label_ids: number[];
}
