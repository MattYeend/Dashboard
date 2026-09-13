<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { updateSettings as organisationsUpdateSettings } from '@/routes/organisations';
import type { OrganisationSettings } from '@/types';

interface Props {
    organisationId: number;
    settings: OrganisationSettings | null;
}

const props = defineProps<Props>();

const form = useForm({
    default_timezone: props.settings?.default_timezone ?? '',
    default_locale: props.settings?.default_locale ?? 'en-GB',
    notification_preferences: {
        email_notifications:
            props.settings?.notification_preferences?.email_notifications ??
            true,
        weekly_digest:
            props.settings?.notification_preferences?.weekly_digest ?? false,
    },
});

function submit(): void {
    form.patch(organisationsUpdateSettings.url(props.organisationId), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Preferences</h2>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <Label for="default_timezone">Default timezone</Label>
                <Input
                    id="default_timezone"
                    v-model="form.default_timezone"
                    type="text"
                    placeholder="Europe/London"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.default_timezone" />
            </div>

            <div>
                <Label for="default_locale">Default locale</Label>
                <Input
                    id="default_locale"
                    v-model="form.default_locale"
                    type="text"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.default_locale" />
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="email_notifications"
                    v-model="form.notification_preferences.email_notifications"
                    type="checkbox"
                />
                <Label for="email_notifications">Email notifications</Label>
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="weekly_digest"
                    v-model="form.notification_preferences.weekly_digest"
                    type="checkbox"
                />
                <Label for="weekly_digest">Weekly digest</Label>
            </div>

            <Button type="submit" :disabled="form.processing">
                Save preferences
            </Button>
        </form>
    </div>
</template>
