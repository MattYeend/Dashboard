<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import NotificationBroadcastForm from '@/pages/NotificationBroadcasts/components/NotificationBroadcastForm.vue';
import { store } from '@/routes/notification-broadcasts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Notifications', href: '/notification-broadcasts' },
            { title: 'Create' },
        ],
    },
});

const form = useForm({
    title: '',
    body: '',
    audience_type: 'all' as 'all' | 'role' | 'users',
    audience_ids: '',
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
    })).post(store().url);
}
</script>

<template>
    <div class="max-w-2xl">
        <h1 class="mb-6 text-lg font-semibold">Create notification</h1>

        <NotificationBroadcastForm
            v-model:title="form.title"
            v-model:body="form.body"
            v-model:audience-type="form.audience_type"
            v-model:audience-ids="form.audience_ids"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Create notification"
            @submit="submit"
        />
    </div>
</template>
