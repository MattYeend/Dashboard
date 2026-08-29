<script setup lang="ts">
import { Button } from '@/components/ui/button';
import NotificationBroadcastAudienceForm from '@/pages/NotificationBroadcasts/components/NotificationBroadcastAudienceForm.vue';
import NotificationBroadcastBasicDetailsForm from '@/pages/NotificationBroadcasts/components/NotificationBroadcastBasicDetailsForm.vue';

interface NotificationBroadcastFormData {
    title: string;
    body: string;
    audience_type: 'all' | 'role' | 'users';
    audience_ids: string;
}

defineProps<{
    errors: Partial<Record<keyof NotificationBroadcastFormData, string>>;
    processing: boolean;
    submitLabel: string;
}>();

const emit = defineEmits<{ submit: [] }>();

const title = defineModel<string>('title', { required: true });
const body = defineModel<string>('body', { required: true });
const audienceType = defineModel<'all' | 'role' | 'users'>('audienceType', {
    required: true,
});
const audienceIds = defineModel<string>('audienceIds', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <NotificationBroadcastBasicDetailsForm
            v-model:title="title"
            v-model:body="body"
            :errors="errors"
        />

        <NotificationBroadcastAudienceForm
            v-model:audience-type="audienceType"
            v-model:audience-ids="audienceIds"
            :errors="errors"
        />

        <Button type="submit" :disabled="processing">{{ submitLabel }}</Button>
    </form>
</template>
