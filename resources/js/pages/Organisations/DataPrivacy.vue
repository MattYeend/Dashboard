<script setup lang="ts">
import OrganisationDataExportCard from '@/pages/Organisations/components/OrganisationDataExportCard.vue';
import OrganisationDeletionCard from '@/pages/Organisations/components/OrganisationDeletionCard.vue';
import type { OrganisationDataPrivacyPermissions } from '@/types';

interface Props {
    organisation: { id: number; name: string };
    permissions: OrganisationDataPrivacyPermissions;
}

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Organisations', href: '/organisations' },
            { title: 'Data privacy', href: '#' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-lg font-semibold text-gray-200">Data privacy</h1>

        <OrganisationDataExportCard
            v-if="permissions.can_export"
            :organisation="organisation"
        />
        <OrganisationDeletionCard
            v-if="permissions.can_delete"
            :organisation="organisation"
        />
    </div>
</template>
