<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { SystemInfo } from '@/types';
import CacheManagement from './components/CacheManagement.vue';
import MaintenanceModeControl from './components/MaintenanceModeControl.vue';
import SystemInfoDetails from './components/SystemInfoDetails.vue';

defineProps<{
    systemInfo: SystemInfo;
    permissions: {
        can_clear_cache: boolean;
        can_run_maintenance: boolean;
        can_view_logs: boolean;
    };
}>();
</script>

<template>
    <Head title="System" />

    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-300">System maintenance</h1>

        <SystemInfoDetails :system-info="systemInfo" />

        <CacheManagement v-if="permissions.can_clear_cache" />

        <MaintenanceModeControl
            v-if="permissions.can_run_maintenance"
            :maintenance-mode="systemInfo.maintenance_mode"
        />

        <a
            v-if="permissions.can_view_logs"
            href="/log-viewer"
            class="text-sm text-gray-300 underline"
        >
            View logs
        </a>
    </div>
</template>
