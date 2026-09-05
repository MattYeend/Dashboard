<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import type { ReportType } from '@/types';
import ReportForm from './components/ReportForm.vue';
import { store as reportsStore } from '@/routes/reports';

defineProps<{
    reportTypes: ReportType[];
    canSchedule: boolean;
}>();

const form = useForm({
    title: '',
    description: '' as string | null,
    type: '',
    format: 'pdf',
    is_scheduled: false,
    schedule_frequency: '' as string | null,
    schedule_time: '' as string | null,
    recipients: '',
});

function submit() {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
        schedule_frequency: data.is_scheduled
            ? nullIfBlank(data.schedule_frequency)
            : null,
        schedule_time: data.is_scheduled
            ? nullIfBlank(data.schedule_time)
            : null,
        recipients: data.is_scheduled
            ? data.recipients
                  .split(',')
                  .map((email) => email.trim())
                  .filter(Boolean)
            : null,
    })).post(reportsStore().url);
}
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <h1 class="text-lg font-semibold text-gray-200">Create Report</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <ReportForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:type="form.type"
                v-model:format="form.format"
                v-model:is-scheduled="form.is_scheduled"
                v-model:schedule-frequency="form.schedule_frequency"
                v-model:schedule-time="form.schedule_time"
                v-model:recipients="form.recipients"
                :errors="form.errors"
                :can-schedule="canSchedule"
                :report-types="reportTypes"
            />

            <button
                type="submit"
                :disabled="form.processing"
                class="text-sm text-gray-200 underline"
            >
                Create Report
            </button>
        </form>
    </div>
</template>
