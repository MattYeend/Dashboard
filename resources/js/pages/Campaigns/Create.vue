<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { prepareCampaignPayload } from '@/lib/campaigns';
import { store as campaignsStore } from '@/routes/campaigns';
import type { SelectOption } from '@/types';
import type { CampaignFormData } from '@/types/forms';
import CampaignForm from './components/CampaignForm.vue';

defineProps<{
    templateOptions: SelectOption[];
    segmentOptions: SelectOption[];
    ownerOptions: SelectOption[];
}>();

const form = useForm<CampaignFormData>({
    name: '',
    email_template_id: '',
    campaign_segment_id: '',
    owner_id: '',
    subject: null,
    from_name: null,
    reply_to: null,
    scheduled_at: null,
});

function onFormUpdate(updated: CampaignFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => prepareCampaignPayload(data)).post(
        campaignsStore.url(),
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Campaign
            </h1>
            <CampaignForm
                :form="form"
                :errors="form.errors"
                :template-options="templateOptions"
                :segment-options="segmentOptions"
                :owner-options="ownerOptions"
                submit-label="Create Campaign"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
