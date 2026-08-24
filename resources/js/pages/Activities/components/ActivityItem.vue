<script setup lang="ts">
import {
    FileText,
    PhoneCall,
    Mail,
    ListChecks,
    RefreshCw,
} from 'lucide-vue-next';
import { computed } from 'vue';
import type { Activity } from '@/types';

interface Props {
    activity: Activity;
}

const props = defineProps<Props>();

const icon = computed(() => {
    switch (props.activity.type) {
        case 'call_logged':
            return PhoneCall;
        case 'email_logged':
            return Mail;
        case 'task_created':
            return ListChecks;
        case 'status_change':
            return RefreshCw;
        default:
            return FileText;
    }
});
</script>

<template>
    <span class="inline-flex items-center gap-1.5 text-sm text-gray-300">
        <component :is="icon" class="h-4 w-4 text-gray-400" />
        {{ activity.type_label }}
    </span>
</template>
