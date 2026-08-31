<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import { update as updateSecurity } from '@/routes/settings/security';
import type { Setting } from '@/types';

interface SecuritySettingsFormData {
    two_factor_required: boolean;
    session_timeout_minutes: number;
    max_login_attempts: number;
    password_expiry_days: string;
}

const props = defineProps<{
    setting: Setting;
    canEdit: boolean;
}>();

const form = useForm<SecuritySettingsFormData>({
    two_factor_required: props.setting.two_factor_required,
    session_timeout_minutes: props.setting.session_timeout_minutes,
    max_login_attempts: props.setting.max_login_attempts,
    password_expiry_days: props.setting.password_expiry_days?.toString() ?? '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        password_expiry_days: numberOrNull(
            nullIfBlank(data.password_expiry_days),
        ),
    })).put(updateSecurity().url, { preserveScroll: true });
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <h2 class="text-sm font-medium text-gray-300">Security</h2>

        <div class="flex items-center gap-2">
            <Checkbox
                id="two_factor_required"
                v-model:checked="form.two_factor_required"
                :disabled="!canEdit"
            />
            <Label for="two_factor_required"
                >Require two-factor authentication</Label
            >
        </div>
        <InputError :message="form.errors.two_factor_required" />

        <div>
            <Label for="session_timeout_minutes"
                >Session timeout (minutes)</Label
            >
            <Input
                id="session_timeout_minutes"
                v-model.number="form.session_timeout_minutes"
                type="number"
                min="5"
                max="1440"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.session_timeout_minutes" />
        </div>

        <div>
            <Label for="max_login_attempts">Maximum login attempts</Label>
            <Input
                id="max_login_attempts"
                v-model.number="form.max_login_attempts"
                type="number"
                min="3"
                max="20"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.max_login_attempts" />
        </div>

        <div>
            <Label for="password_expiry_days">Password expiry (days)</Label>
            <Input
                id="password_expiry_days"
                v-model="form.password_expiry_days"
                type="number"
                min="30"
                max="365"
                placeholder="Never"
                :disabled="!canEdit"
            />
            <InputError :message="form.errors.password_expiry_days" />
        </div>

        <button v-if="canEdit" type="submit" :disabled="form.processing">
            Save
        </button>
    </form>
</template>
