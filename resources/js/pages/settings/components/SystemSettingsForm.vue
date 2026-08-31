<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update as updateSystem } from '@/routes/settings/system';
import type { Setting } from '@/types';

interface SystemSettingsFormData {
    maintenance_mode: boolean;
    allow_registrations: boolean;
    default_pagination: number;
    default_locale: string;
}

const props = defineProps<{
    setting: Setting;
    canEdit: boolean;
}>();

const form = useForm<SystemSettingsFormData>({
    maintenance_mode: props.setting.maintenance_mode,
    allow_registrations: props.setting.allow_registrations,
    default_pagination: props.setting.default_pagination,
    default_locale: props.setting.default_locale,
});

const submit = () => {
    form.put(updateSystem().url, { preserveScroll: true });
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <h2 class="text-sm font-medium text-gray-300">System</h2>

        <div class="flex items-center gap-2">
            <Checkbox
                id="maintenance_mode"
                v-model:checked="form.maintenance_mode"
                :disabled="!canEdit"
            />
            <Label for="maintenance_mode">Maintenance mode</Label>
        </div>
        <InputError :message="form.errors.maintenance_mode" />

        <div class="flex items-center gap-2">
            <Checkbox
                id="allow_registrations"
                v-model:checked="form.allow_registrations"
                :disabled="!canEdit"
            />
            <Label for="allow_registrations">Allow registrations</Label>
        </div>
        <InputError :message="form.errors.allow_registrations" />

        <div>
            <Label for="default_pagination">Default pagination</Label>
            <Input
                id="default_pagination"
                v-model.number="form.default_pagination"
                type="number"
                min="5"
                max="100"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.default_pagination" />
        </div>

        <div>
            <Label for="default_locale">Default locale</Label>
            <Input
                id="default_locale"
                v-model="form.default_locale"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.default_locale" />
        </div>

        <button v-if="canEdit" type="submit" :disabled="form.processing">
            Save
        </button>
    </form>
</template>
