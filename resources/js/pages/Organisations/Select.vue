<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { Organisation } from '@/types';

interface Props {
    organisations: Pick<Organisation, 'id' | 'name'>[];
}

defineProps<Props>();

function selectOrganisation(id: number): void {
    router.post(`/organisations/${id}/switch`);
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-md px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Select an organisation</h1>

            <p v-if="!organisations.length" class="text-sm text-gray-400">
                You don't belong to any organisations yet.
            </p>

            <ul v-else class="space-y-2">
                <li v-for="organisation in organisations" :key="organisation.id">
                    <button
                        type="button"
                        class="w-full rounded-md border px-4 py-2 text-left text-sm font-medium"
                        @click="selectOrganisation(organisation.id)"
                    >
                        {{ organisation.name }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>