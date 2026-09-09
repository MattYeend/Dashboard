<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { Organisation } from '@/types';

interface Props {
    currentOrganisationId: number;
    organisations: Pick<Organisation, 'id' | 'name'>[];
}

defineProps<Props>();

function switchTo(id: number): void {
    router.post(`/organisations/${id}/switch`);
}
</script>

<template>
    <select
        :value="currentOrganisationId"
        class="rounded-md border border-gray-500 px-2 py-1 text-sm text-gray-300"
        @change="switchTo(Number(($event.target as HTMLSelectElement).value))"
    >
        <option
            v-for="organisation in organisations"
            :key="organisation.id"
            :value="organisation.id"
        >
            {{ organisation.name }}
        </option>
    </select>
</template>
