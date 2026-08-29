<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import NotificationBroadcastForm from '@/pages/NotificationBroadcasts/components/NotificationBroadcastForm.vue';
import { update } from '@/routes/notification-broadcasts';
import type { NotificationBroadcast } from '@/types';

const props = defineProps<{ notificationBroadcast: NotificationBroadcast }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Notifications', href: '/notification-broadcasts' },
            { title: 'Edit' },
        ],
    },
});

const form = useForm({
    title: props.notificationBroadcast.title,
    body: props.notificationBroadcast.body,
    audience_type: props.notificationBroadcast.audience_type,
    audience_ids: props.notificationBroadcast.audience_ids?.join(', ') ?? '',
});

function submit() {
    form.transform((data) => ({
        ...data,
        audience_ids:
            data.audience_type === 'all'
                ? null
                : (nullIfBlank(data.audience_ids)
                      ?.split(',')
                      .map((value) => value.trim())
                      .filter(Boolean) ?? null),
    })).put(update(props.notificationBroadcast.id).url);
}
</script>

<template>
    <div class="max-w-2xl">
        <h1 class="mb-6 text-lg font-semibold">Edit notification</h1>

        <NotificationBroadcastForm
            v-model:title="form.title"
            v-model:body="form.body"
            v-model:audience-type="form.audience_type"
            v-model:audience-ids="form.audience_ids"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Save changes"
            @submit="submit"
        />
    </div>
</template>
