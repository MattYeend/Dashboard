<script setup lang="ts">
import type { PageProps } from '@inertiajs/core';
import { usePage } from '@inertiajs/vue3';
import OrganisationBrandingForm from '@/pages/Organisations/components/OrganisationBrandingForm.vue';
import OrganisationPreferencesForm from '@/pages/Organisations/components/OrganisationPreferencesForm.vue';
import type { Organisation } from '@/types';

interface Props {
    organisation: Organisation;
    logo_download_url: string | null;
}

interface AppPageProps extends PageProps {
    flash: {
        success?: string;
        error?: string;
    };
}

defineProps<Props>();

const page = usePage<AppPageProps>();
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold">Organisation settings</h1>

            <p
                v-if="page.props.flash.success"
                class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
            >
                {{ page.props.flash.success }}
            </p>

            <p
                v-if="page.props.flash.error"
                class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ page.props.flash.error }}
            </p>

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
