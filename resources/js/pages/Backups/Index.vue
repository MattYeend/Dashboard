<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { Backup } from '@/types';
import BackupList from './components/BackupList.vue';
import CreateBackupControl from './components/CreateBackupControl.vue';
import ImportBackupForm from './components/ImportBackupForm.vue';

defineProps<{
    backups: Backup[];
    permissions: {
        can_create: boolean;
        can_restore: boolean;
        can_delete: boolean;
        can_import: boolean;
        can_export: boolean;
    };
}>();
</script>

<template>
    <Head title="Backups" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">Backups</h1>
                <CreateBackupControl v-if="permissions.can_create" />
            </div>

            <ImportBackupForm v-if="permissions.can_import" />

            <BackupList
                :backups="backups"
                :can-restore="permissions.can_restore"
                :can-delete="permissions.can_delete"
                :can-export="permissions.can_export"
            />
        </div>
    </div>
</template>
