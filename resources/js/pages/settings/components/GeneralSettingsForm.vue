<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update as updateGeneral } from '@/routes/settings/general';
import type { Setting } from '@/types';

interface GeneralSettingsFormData {
    site_name: string;
    support_email: string;
    timezone: string;
    date_format: string;
}

const props = defineProps<{
    setting: Setting;
    canEdit: boolean;
}>();

const form = useForm<GeneralSettingsFormData>({
    site_name: props.setting.site_name,
    support_email: props.setting.support_email,
    timezone: props.setting.timezone,
    date_format: props.setting.date_format,
});

const submit = () => {
    form.put(updateGeneral().url, { preserveScroll: true });
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <h2 class="text-sm font-medium text-gray-300">General</h2>

        <div>
            <Label for="site_name">Site name</Label>
            <Input
                id="site_name"
                v-model="form.site_name"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.site_name" />
        </div>

        <div>
            <Label for="support_email">Support email</Label>
            <Input
                id="support_email"
                v-model="form.support_email"
                type="email"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.support_email" />
        </div>

        <div>
            <Label for="timezone">Timezone</Label>
            <Input id="timezone" v-model="form.timezone" :disabled="!canEdit" />
            <InputError :message="form.errors.timezone" />
        </div>

        <div>
            <Label for="date_format">Date format</Label>
            <Input
                id="date_format"
                v-model="form.date_format"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.date_format" />
        </div>

        <button v-if="canEdit" type="submit" :disabled="form.processing">
            Save
        </button>
    </form>
</template>
