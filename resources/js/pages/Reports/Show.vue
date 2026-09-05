<script setup lang="ts">
import ReportBasicDetails from './components/ReportBasicDetails.vue';
import ReportScheduleDetails from './components/ReportScheduleDetails.vue';
import ReportAuditDetails from './components/ReportAuditDetails.vue';
import type { Report } from '@/types';

defineProps<{ report: Report }>();
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <ReportBasicDetails :report="report" />
        <ReportScheduleDetails :report="report" />
        <ReportAuditDetails :report="report" />
    </div>
</template>