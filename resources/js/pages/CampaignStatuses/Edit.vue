<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { update as campaignStatusesUpdate } from '@/routes/campaign-statuses';
import type { CampaignStatus } from '@/types';
import type { CampaignStatusFormData } from '@/types/forms';
import CampaignStatusForm from './components/CampaignStatusForm.vue';

const props = defineProps<{
    campaignStatus: CampaignStatus;
}>();

const form = useForm<CampaignStatusFormData>({
    title: props.campaignStatus.title,
    description: props.campaignStatus.description,
    background_colour: props.campaignStatus.background_colour,
    text_colour: props.campaignStatus.text_colour,
});

function onFormUpdate(updated: CampaignStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).put(campaignStatusesUpdate.url(props.campaignStatus.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Campaign Status
            </h1>
            <CampaignStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Campaign Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>