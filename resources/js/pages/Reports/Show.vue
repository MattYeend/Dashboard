<script setup lang="ts">
import type { Report } from '@/types';
import ReportAuditDetails from './components/ReportAuditDetails.vue';
import ReportBasicDetails from './components/ReportBasicDetails.vue';
import ReportScheduleDetails from './components/ReportScheduleDetails.vue';

defineProps<{ report: Report }>();
</script>

<template>
    <div class="max-w-2xl space-y-6">
        <ReportBasicDetails :report="report" />
        <ReportScheduleDetails :report="report" />
        <ReportAuditDetails :report="report" />
    </div>
</template>
