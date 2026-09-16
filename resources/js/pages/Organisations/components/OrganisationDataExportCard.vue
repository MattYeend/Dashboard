<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

interface Props {
    organisation: { id: number; name: string };
}

const props = defineProps<Props>();

const form = useForm({});

function requestExport(): void {
    form.post(`/organisations/${props.organisation.id}/data-privacy/export`);
}
</script>

<template>
    <div class="rounded border border-gray-500 p-4">
        <h2 class="text-sm font-semibold text-gray-300">
            Export my organisation's data
        </h2>
        <p class="mt-1 text-xs text-gray-400">
            Request a full export of every record belonging to
            {{ organisation.name }}. You will be notified in the app and by
            email once it is ready to download.
        </p>
        <button
            type="button"
            class="mt-3 rounded border border-gray-500 px-3 py-1.5 text-xs text-gray-300"
            :disabled="form.processing"
            @click="requestExport"
        >
            Request data export
        </button>
    </div>
</template>
