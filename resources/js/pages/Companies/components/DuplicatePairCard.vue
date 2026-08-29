<script setup lang="ts">
interface CompanyDuplicateSummary {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
}

interface Props {
    company: CompanyDuplicateSummary;
    duplicate: CompanyDuplicateSummary;
    reason: string;
    canMerge: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    merge: [];
}>();
</script>

<template>
    <div class="rounded-md border border-gray-500 p-4">
        <p class="text-xs text-gray-400">{{ reason }}</p>

        <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-gray-300">
                    {{ company.name }}
                </p>
                <p class="text-xs text-gray-400">{{ company.email ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ company.phone ?? '-' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-300">
                    {{ duplicate.name }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ duplicate.email ?? '-' }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ duplicate.phone ?? '-' }}
                </p>
            </div>
        </div>

        <button
            v-if="canMerge"
            type="button"
            class="mt-4 rounded-md border border-gray-500 px-3 py-1.5 text-sm font-medium text-gray-300 hover:border-gray-400"
            @click="emit('merge')"
        >
            Merge into left record
        </button>
    </div>
</template>
