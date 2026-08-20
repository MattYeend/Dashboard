<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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
    maintenanceBypassUrl?: string;
}>();
</script>

<template>
    <Head title="System" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-xl font-semibold text-gray-300">System maintenance</h1>

            <SystemInfoDetails :system-info="systemInfo" />

            <CacheManagement v-if="permissions.can_clear_cache" />

            <MaintenanceModeControl
                v-if="permissions.can_run_maintenance"
                :maintenance-mode="systemInfo.maintenance_mode"
                :bypass-url="maintenanceBypassUrl"
            />

            <Button v-if="permissions.can_view_logs" variant="outline" as-child>
                <a href="/log-viewer">View logs</a>
            </Button>
        </div>
    </div>
</template>
