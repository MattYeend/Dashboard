<script setup lang="ts">
import type { Organisation } from '@/types';
import OrganisationBrandingForm from '@/pages/Organisations/components/OrganisationBrandingForm.vue';
import OrganisationPreferencesForm from '@/pages/Organisations/components/OrganisationPreferencesForm.vue';

interface Props {
    organisation: Organisation;
    logo_download_url: string | null;
}

defineProps<Props>();
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold">Organisation settings</h1>

            <OrganisationBrandingForm
                :organisation-id="organisation.id"
                :settings="organisation.settings"
                :logo-download-url="logo_download_url"
            />

            <OrganisationPreferencesForm
                :organisation-id="organisation.id"
                :settings="organisation.settings"
            />
        </div>
    </div>
</template>
