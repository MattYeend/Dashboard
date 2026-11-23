<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { prepareCampaignSegmentPayload } from '@/lib/campaign-segments';
import { store as campaignSegmentsStore } from '@/routes/campaign-segments';
import type { SelectOption } from '@/types';
import type { CampaignSegmentFormData } from '@/types/forms';
import CampaignSegmentForm from './components/CampaignSegmentForm.vue';
 
defineProps<{
    fieldOptions: SelectOption[];
    operatorOptions: SelectOption[];
}>();
 
const form = useForm<CampaignSegmentFormData>({
    name: '',
    description: null,
    criteria: [],
});
 
function onFormUpdate(updated: CampaignSegmentFormData): void {
    Object.assign(form, updated);
}
 
function submit(): void {
    form.transform((data) => prepareCampaignSegmentPayload(data)).post(
        campaignSegmentsStore.url(),
    );
}
</script>
 
<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Campaign Segment
            </h1>
            <CampaignSegmentForm
                :form="form"
                :errors="form.errors"
                :field-options="fieldOptions"
                :operator-options="operatorOptions"
                submit-label="Create Campaign Segment"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>