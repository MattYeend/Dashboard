<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import TagPicker from '@/components/TagPicker.vue';
import { Button } from '@/components/ui/button';
import DealBasicDetailsForm from '@/pages/Deals/components/DealBasicDetailsForm.vue';
import DealPipelineDetailsForm from '@/pages/Deals/components/DealPipelineDetailsForm.vue';
import DealRelationsDetailsForm from '@/pages/Deals/components/DealRelationsDetailsForm.vue';
import DealValueDetailsForm from '@/pages/Deals/components/DealValueDetailsForm.vue';
import { index as dealsIndex } from '@/routes/deals';
import type {
    Company,
    DealStatus,
    Invoice,
    Pipeline,
    PipelineStage,
} from '@/types';

interface DealFormData {
    title: string;
    description: string | null;
    pipeline_id: number | null;
    stage_id: number | null;
    status_id: number | null;
    company_id: number | null;
    invoice_id: number | null;
    value: number;
    currency: string;
    probability: number;
    expected_close_date: string | null;
    closed_at: string | null;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<DealFormData>['errors']>;
    pipelines: Pipeline[];
    pipelineStages: PipelineStage[];
    statuses: DealStatus[];
    companies: Company[];
    invoices: Invoice[];
    tags: { id: number; name: string }[];
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const pipelineId = defineModel<number | null>('pipelineId', { default: null });
const stageId = defineModel<number | null>('stageId', { default: null });
const statusId = defineModel<number | null>('statusId', { default: null });
const companyId = defineModel<number | null>('companyId', { default: null });
const invoiceId = defineModel<number | null>('invoiceId', { default: null });
const value = defineModel<number>('value', { required: true });
const currency = defineModel<string>('currency', { required: true });
const probability = defineModel<number>('probability', { required: true });
const expectedCloseDate = defineModel<string | null>('expectedCloseDate', {
    default: null,
});
const closedAt = defineModel<string | null>('closedAt', { default: null });
const tagIds = defineModel<number[]>('tagIds', { default: () => [] });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <DealBasicDetailsForm
            v-model:title="title"
            v-model:description="description"
            :errors="errors"
        />

        <DealPipelineDetailsForm
            v-model:pipeline-id="pipelineId"
            v-model:stage-id="stageId"
            v-model:status-id="statusId"
            :pipelines="pipelines"
            :pipeline-stages="pipelineStages"
            :statuses="statuses"
            :errors="errors"
        />

        <DealRelationsDetailsForm
            v-model:company-id="companyId"
            v-model:invoice-id="invoiceId"
            :companies="companies"
            :invoices="invoices"
            :errors="errors"
        />

        <DealValueDetailsForm
            v-model:value="value"
            v-model:currency="currency"
            v-model:probability="probability"
            v-model:expected-close-date="expectedCloseDate"
            v-model:closed-at="closedAt"
            :errors="errors"
        />

        <TagPicker v-model:tag-ids="tagIds" :tags="tags" :can-create="true" />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="dealsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Deal' : 'Create Deal' }}
            </Button>
        </div>
    </form>
</template>
