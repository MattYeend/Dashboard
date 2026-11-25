<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { toDateTimeLocal } from '@/lib/dates';
import { prepareCampaignPayload } from '@/lib/forms';
import { update as campaignsUpdate } from '@/routes/campaigns';
import type { Campaign, SelectOption } from '@/types';
import type { CampaignFormData } from '@/types/forms';
import CampaignForm from './components/CampaignForm.vue';

const props = defineProps<{
    campaign: Campaign;
    templateOptions: SelectOption[];
    segmentOptions: SelectOption[];
    ownerOptions: SelectOption[];
}>();

const form = useForm<CampaignFormData>({
    name: props.campaign.name,
    email_template_id: String(props.campaign.email_template_id ?? ''),
    campaign_segment_id: String(props.campaign.campaign_segment_id ?? ''),
    owner_id: String(props.campaign.owner_id ?? ''),
    subject: props.campaign.subject,
    from_name: props.campaign.from_name,
    reply_to: props.campaign.reply_to,
    scheduled_at: toDateTimeLocal(props.campaign.scheduled_at),
});

function onFormUpdate(updated: CampaignFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => prepareCampaignPayload(data)).put(
        campaignsUpdate.url(props.campaign.id),
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Campaign
            </h1>
            <CampaignForm
                :form="form"
                :errors="form.errors"
                :template-options="templateOptions"
                :segment-options="segmentOptions"
                :owner-options="ownerOptions"
                submit-label="Update Campaign"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
