export interface ImportPreviewRow {
    row: number;
    data: Record<string, unknown>;
    valid: boolean;
    reason: string | null;
}

export interface ImportPreviewResponse {
    token: string;
    columns: string[];
    rows: ImportPreviewRow[];
    valid_count: number;
    skipped_count: number;
}

export interface ImportCommitResponse {
    imported: number;
    skipped: Array<{ row: number; reason: string }>;
}