<script setup lang="ts">
import type { Report } from '@/types';

defineProps<{ report: Report }>();
</script>

<template>
    <div>
        <h2 class="text-sm font-semibold text-gray-300">Schedule</h2>
        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-2">
            <dt class="text-xs text-gray-400">Scheduled</dt>
            <dd class="text-sm text-gray-200">
                {{ report.is_scheduled ? 'Yes' : 'No' }}
            </dd>

            <template v-if="report.is_scheduled">
                <dt class="text-xs text-gray-400">Frequency</dt>
                <dd class="text-sm text-gray-200 capitalize">
                    {{ report.schedule_frequency }}
                </dd>

                <dt class="text-xs text-gray-400">Time</dt>
                <dd class="text-sm text-gray-200">
                    {{ report.schedule_time }}
                </dd>

                <dt class="text-xs text-gray-400">Recipients</dt>
                <dd class="text-sm text-gray-200">
                    {{ (report.recipients ?? []).join(', ') || '—' }}
                </dd>

                <dt class="text-xs text-gray-400">Next run</dt>
                <dd class="text-sm text-gray-200">
                    {{ report.next_run_at ?? '—' }}
                </dd>
            </template>

            <dt class="text-xs text-gray-400">Last run</dt>
            <dd class="text-sm text-gray-200">
                {{ report.last_run_at ?? 'Never' }}
            </dd>
        </dl>
    </div>
</template>
