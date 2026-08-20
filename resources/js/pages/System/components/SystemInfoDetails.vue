<script setup lang="ts">
import type { SystemInfo } from '@/types';

defineProps<{
    systemInfo: SystemInfo;
}>();
</script>

<template>
    <div class="rounded border border-gray-500 p-4">
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-400">PHP version</dt>
                <dd class="text-gray-300">{{ systemInfo.php_version }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Laravel version</dt>
                <dd class="text-gray-300">{{ systemInfo.laravel_version }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Environment</dt>
                <dd class="text-gray-300">{{ systemInfo.environment }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Database</dt>
                <dd class="text-gray-300">
                    {{ systemInfo.database_connection }}
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Cache driver</dt>
                <dd class="text-gray-300">{{ systemInfo.cache_driver }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Queue driver</dt>
                <dd class="text-gray-300">{{ systemInfo.queue_driver }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Disk free / total</dt>
                <dd class="text-gray-300">
                    {{ systemInfo.disk_free_space }} /
                    {{ systemInfo.disk_total_space }}
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Server time</dt>
                <dd class="text-gray-300">{{ systemInfo.server_time }}</dd>
            </div>
        </dl>
    </div>
</template>
