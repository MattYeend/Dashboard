<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as campaignStatusesStore } from '@/routes/campaign-statuses';
import type { CampaignStatusFormData } from '@/types/forms';
import CampaignStatusForm from './components/CampaignStatusForm.vue';

const form = useForm<CampaignStatusFormData>({
    title: '',
    description: null,
    background_colour: '#ffffff',
    text_colour: '#000000',
});

function onFormUpdate(updated: CampaignStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(campaignStatusesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Campaign Status
            </h1>
            <CampaignStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Campaign Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
