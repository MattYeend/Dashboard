<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ImportPreviewTable from '@/components/ImportPreviewTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useImportWorkflow } from '@/composables/useImportWorkflow';
import { index as ticketStatusesIndex } from '@/routes/ticket-statuses';
import { preview as importPreview, commit as importCommit } from '@/routes/ticket-statuses/import';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Ticket Statuses', href: ticketStatusesIndex().url }, { title: 'Import' }] },
});

const file = ref<File | null>(null);
const confirmOpen = ref(false);

const { preview, processing, error, runPreview, runCommit, reset } = useImportWorkflow(
    importPreview().url,
    importCommit().url,
);

function onFileChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    file.value = input.files?.[0] ?? null;
}

async function onUpload(): Promise<void> {
    if (!file.value) {
        return;
    }

    await runPreview(file.value);
}

async function onConfirmCommit(): Promise<void> {
    const result = await runCommit();
    confirmOpen.value = false;

    if (result) {
        router.visit(ticketStatusesIndex().url, {
            data: { imported: result.imported, skipped: result.skipped.length },
        });
    }
}

function onCancel(): void {
    reset();
    file.value = null;
}
</script>

<template>
    <div class="space-y-6 p-6">
        <h1 class="text-lg font-semibold text-gray-200">Import ticket statuses</h1>

        <div v-if="!preview" class="space-y-4">
            <Input type="file" accept=".csv,.txt" @change="onFileChange" />
            <p v-if="error" class="text-sm text-red-400">{{ error }}</p>
            <Button :disabled="!file || processing" @click="onUpload">
                {{ processing ? 'Processing…' : 'Preview import' }}
            </Button>
        </div>

        <div v-else class="space-y-4">
            <p class="text-sm text-gray-300">
                {{ preview.valid_count }} row(s) valid, {{ preview.skipped_count }} row(s) will be skipped.
            </p>

            <ImportPreviewTable :columns="preview.columns" :rows="preview.rows" />

            <p v-if="error" class="text-sm text-red-400">{{ error }}</p>

            <div class="flex gap-3">
                <Button variant="outline" type="button" :disabled="processing" @click="onCancel">Cancel</Button>
                <Button type="button" :disabled="processing || preview.valid_count === 0" @click="confirmOpen = true">
                    Continue
                </Button>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            title="Confirm import"
            :description="`Import ${preview?.valid_count ?? 0} valid row(s)? Skipped rows will not be created.`"
            confirm-label="Import"
            :processing="processing"
            @confirm="onConfirmCommit"
        />
    </div>
</template>