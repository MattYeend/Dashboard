import axios from 'axios';
import { ref } from 'vue';
import type { ImportCommitResponse, ImportPreviewResponse } from '@/types';

export function useImportWorkflow(previewUrl: string, commitUrl: string) {
    const preview = ref<ImportPreviewResponse | null>(null);
    const processing = ref(false);
    const error = ref<string | null>(null);

    async function runPreview(file: File): Promise<void> {
        processing.value = true;
        error.value = null;

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post<ImportPreviewResponse>(previewUrl, formData);
            preview.value = response.data;
        } catch (e) {
            error.value = axios.isAxiosError(e)
                ? (e.response?.data?.message ?? 'The file could not be processed.')
                : 'The file could not be processed.';
        } finally {
            processing.value = false;
        }
    }

    async function runCommit(): Promise<ImportCommitResponse | null> {
        if (!preview.value) {
            return null;
        }

        processing.value = true;
        error.value = null;

        try {
            const response = await axios.post<ImportCommitResponse>(commitUrl, { token: preview.value.token });
            preview.value = null;

            return response.data;
        } catch (e) {
            error.value = axios.isAxiosError(e)
                ? (e.response?.data?.message ?? 'The import could not be committed.')
                : 'The import could not be committed.';
                
            return null;
        } finally {
            processing.value = false;
        }
    }

    function reset(): void {
        preview.value = null;
        error.value = null;
    }

    return { preview, processing, error, runPreview, runCommit, reset };
}