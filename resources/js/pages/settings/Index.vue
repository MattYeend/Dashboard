<script setup lang="ts">
import GeneralSettingsForm from './components/GeneralSettingsForm.vue';
import SecuritySettingsForm from './components/SecuritySettingsForm.vue';
import SystemSettingsForm from './components/SystemSettingsForm.vue';
import type { Setting, SettingsPermissions } from '@/types';
import { index as settingsIndex } from '@/routes/settings';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Settings', href: settingsIndex().url }] },
});

defineProps<{
    setting: Setting;
    permissions: SettingsPermissions;
}>();
</script>

<template>
    <div class="space-y-8">
        <h1 class="text-lg font-semibold">Settings</h1>

        <GeneralSettingsForm
            v-if="permissions.can_view_general"
            :setting="setting"
            :can-edit="permissions.can_edit_general"
        />

        <SystemSettingsForm
            v-if="permissions.can_view_system"
            :setting="setting"
            :can-edit="permissions.can_edit_system"
        />

        <SecuritySettingsForm
            v-if="permissions.can_view_security"
            :setting="setting"
            :can-edit="permissions.can_edit_security"
        />
    </div>
</template>
