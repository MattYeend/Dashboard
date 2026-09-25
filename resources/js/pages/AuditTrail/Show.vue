<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { index } from '@/routes/audit-trail';
import type { AuditLogEntry } from '@/types';
import AuditEntryDetails from './components/AuditEntryDetails.vue';
import AuditEntryIntegrity from './components/AuditEntryIntegrity.vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Audit trail', href: index().url }],
    },
});

defineProps<{
    log: AuditLogEntry;
}>();
</script>

<template>
    <Head :title="`Audit entry #${log.id}`" />

    <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold">Audit entry #{{ log.id }}</h1>

        <AuditEntryDetails :log="log" />
        <AuditEntryIntegrity :log="log" />
    </div>
</template>