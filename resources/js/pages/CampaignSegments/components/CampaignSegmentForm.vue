<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as campaignSegmentsIndex } from '@/routes/campaign-segments';
import type { SelectOption } from '@/types';
import type { CampaignSegmentFormData } from '@/types/forms';
import CampaignSegmentBasicDetailsForm from './CampaignSegmentBasicDetailsForm.vue';
import CampaignSegmentCriteriaForm from './CampaignSegmentCriteriaForm.vue';

defineProps<{
    form: CampaignSegmentFormData;
    errors: Partial<Record<string, string>>;
    fieldOptions: SelectOption[];
    operatorOptions: SelectOption[];
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: CampaignSegmentFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <CampaignSegmentBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', $event)"
        />
        <CampaignSegmentCriteriaForm
            :form="form"
            :errors="errors"
            :field-options="fieldOptions"
            :operator-options="operatorOptions"
            @update:form="emit('update:form', $event)"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="campaignSegmentsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
