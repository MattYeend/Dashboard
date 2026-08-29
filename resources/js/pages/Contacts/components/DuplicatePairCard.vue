<script setup lang="ts">
interface ContactDuplicateSummary {
    id: number;
    contactable_name: string | null;
    email: string | null;
    phone: string | null;
}

interface Props {
    contact: ContactDuplicateSummary;
    duplicate: ContactDuplicateSummary;
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
                    {{ contact.contactable_name ?? 'Unknown' }}
                </p>
                <p class="text-xs text-gray-400">{{ contact.email ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ contact.phone ?? '-' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-300">
                    {{ duplicate.contactable_name ?? 'Unknown' }}
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
