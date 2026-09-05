<script setup lang="ts">
import type { ReportType } from '@/types';
import ReportBasicDetailsForm from './ReportBasicDetailsForm.vue';
import ReportScheduleDetailsForm from './ReportScheduleDetailsForm.vue';

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    required: true,
});
const type = defineModel<string>('type', { required: true });
const format = defineModel<string>('format', { required: true });
const isScheduled = defineModel<boolean>('isScheduled', { required: true });
const scheduleFrequency = defineModel<string | null>('scheduleFrequency', {
    required: true,
});
const scheduleTime = defineModel<string | null>('scheduleTime', {
    required: true,
});
const recipients = defineModel<string>('recipients', { required: true });

defineProps<{
    errors: Partial<Record<string, string>>;
    canSchedule: boolean;
    reportTypes: ReportType[];
}>();
</script>

<template>
    <div class="space-y-6">
        <ReportBasicDetailsForm
            v-model:title="title"
            v-model:description="description"
            v-model:type="type"
            v-model:format="format"
            :errors="errors"
            :report-types="reportTypes"
        />
        <ReportScheduleDetailsForm
            v-model:is-scheduled="isScheduled"
            v-model:schedule-frequency="scheduleFrequency"
            v-model:schedule-time="scheduleTime"
            v-model:recipients="recipients"
            :errors="errors"
            :can-schedule="canSchedule"
        />
    </div>
</template>
