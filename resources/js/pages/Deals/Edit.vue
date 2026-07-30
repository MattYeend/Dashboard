<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import DealForm from '@/pages/Deals/components/DealForm.vue';
import type {
    Company,
    Deal,
    DealStatus,
    Invoice,
    Pipeline,
    PipelineStage,
} from '@/types';
import { update as dealsUpdate } from '@/routes/deals';

interface Props {
    deal: Deal;
    pipelines: Pipeline[];
    pipeline_stages: PipelineStage[];
    deal_statuses: DealStatus[];
    companies: Company[];
    invoices: Invoice[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.deal.title,
    description: props.deal.description ?? '',
    pipeline_id: props.deal.pipeline_id,
    stage_id: props.deal.stage_id,
    status_id: props.deal.status_id,
    company_id: props.deal.company_id,
    invoice_id: props.deal.invoice_id,
    value: props.deal.value,
    currency: props.deal.currency,
    probability: props.deal.probability,
    expected_close_date: props.deal.expected_close_date ?? '',
    closed_at: props.deal.closed_at ?? '',
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        pipeline_id: numberOrNull(data.pipeline_id),
        stage_id: numberOrNull(data.stage_id),
        status_id: numberOrNull(data.status_id),
        company_id: numberOrNull(data.company_id),
        invoice_id: numberOrNull(data.invoice_id),
        expected_close_date: nullIfBlank(data.expected_close_date),
        closed_at: nullIfBlank(data.closed_at),
    })).put(dealsUpdate.url(props.deal.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">Edit Deal</h1>

            <DealForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:pipeline-id="form.pipeline_id"
                v-model:stage-id="form.stage_id"
                v-model:status-id="form.status_id"
                v-model:company-id="form.company_id"
                v-model:invoice-id="form.invoice_id"
                v-model:value="form.value"
                v-model:currency="form.currency"
                v-model:probability="form.probability"
                v-model:expected-close-date="form.expected_close_date"
                v-model:closed-at="form.closed_at"
                :pipelines="pipelines"
                :pipeline-stages="pipeline_stages"
                :statuses="deal_statuses"
                :companies="companies"
                :invoices="invoices"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
