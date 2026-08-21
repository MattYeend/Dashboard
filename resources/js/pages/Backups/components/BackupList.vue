<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import type { Backup } from '@/types';
import { destroy, download, restore } from '@/routes/backups';

interface BackupRow extends Backup {
    id: string;
}

const props = defineProps<{
    backups: Backup[];
    canRestore: boolean;
    canDelete: boolean;
    canExport: boolean;
}>();

const rows = props.backups.map((backup) => ({
    ...backup,
    id: backup.filename,
}));

const columns: ResourceTableColumn[] = [
    { key: 'filename', label: 'Filename' },
    { key: 'disk', label: 'Disk' },
    { key: 'size_human', label: 'Size' },
    { key: 'date', label: 'Created' },
];

const deleteDialogOpen = ref(false);
const restoreDialogOpen = ref(false);
const selectedFilename = ref<string | null>(null);
const processing = ref(false);

function requestDelete(row: BackupRow): void {
    selectedFilename.value = row.filename;
    deleteDialogOpen.value = true;
}

function requestRestore(row: BackupRow): void {
    selectedFilename.value = row.filename;
    restoreDialogOpen.value = true;
}

function confirmDelete(): void {
    if (!selectedFilename.value) {
        return;
    }

    processing.value = true;

    router.delete(destroy(selectedFilename.value).url, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            deleteDialogOpen.value = false;
            selectedFilename.value = null;
        },
    });
}

function confirmRestore(): void {
    if (!selectedFilename.value) {
        return;
    }

    processing.value = true;

    router.post(
        restore(selectedFilename.value).url,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
                restoreDialogOpen.value = false;
                selectedFilename.value = null;
            },
        },
    );
}
</script>

<template>
    <div>
        <ResourceTable
            :rows="rows"
            :columns="columns"
            row-key="id"
            empty-message="No backups found."
        >
            <template #actions="{ row }">
                <Link
                    v-if="canExport"
                    :href="download(row.filename).url"
                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-gray-300"
                >
                    Download
                </Link>
                <button
                    v-if="canRestore"
                    type="button"
                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-amber-500"
                    @click="requestRestore(row)"
                >
                    Restore
                </button>
                <button
                    v-if="canDelete"
                    type="button"
                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                    @click="requestDelete(row)"
                >
                    Delete
                </button>
            </template>
        </ResourceTable>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete backup"
            description="This backup file will be permanently removed and cannot be recovered."
            confirm-label="Delete"
            :processing="processing"
            @confirm="confirmDelete"
        />

        <ConfirmDialog
            v-model:open="restoreDialogOpen"
            title="Restore database from backup"
            description="This will overwrite the current database with the contents of this backup. This cannot be undone."
            confirm-label="Restore"
            :processing="processing"
            @confirm="confirmRestore"
        />
    </div>
</template>
