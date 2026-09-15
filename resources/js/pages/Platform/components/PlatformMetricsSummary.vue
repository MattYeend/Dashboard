<script setup lang="ts">
defineProps<{
    organisationCount: number;
    totalActiveMembers: number;
}>();
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-md border border-gray-500 p-4">
            <dl>
                <dt class="text-xs text-gray-400">Total organisations</dt>
                <dd class="text-2xl text-gray-300">{{ organisationCount }}</dd>
            </dl>
        </div>

        <div class="rounded-md border border-gray-500 p-4">
            <dl>
                <dt class="text-xs text-gray-400">Total active members</dt>
                <dd class="text-2xl text-gray-300">{{ totalActiveMembers }}</dd>
            </dl>
        </div>
    </div>
</template>
