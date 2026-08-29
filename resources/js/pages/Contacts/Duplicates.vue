<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { merge as contactsMerge } from '@/routes/contacts';
import DuplicatePairCard from './components/DuplicatePairCard.vue';

interface ContactDuplicateSummary {
    id: number;
    contactable_name: string | null;
    email: string | null;
    phone: string | null;
}

interface ContactDuplicateCandidate {
    contact: ContactDuplicateSummary;
    duplicate: ContactDuplicateSummary;
    reason: string;
}

interface Props {
    candidates: ContactDuplicateCandidate[];
    permissions_meta: {
        can_merge: boolean;
    };
}

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'contacts', href: '/contacts' },
            { title: 'Duplicates', href: '/contacts/duplicates' },
        ],
    },
});

const mergeDialogOpen = ref(false);
const mergeProcessing = ref(false);
const pendingSurvivorId = ref<number | null>(null);
const pendingDuplicateId = ref<number | null>(null);

function requestMerge(survivorId: number, duplicateId: number): void {
    pendingSurvivorId.value = survivorId;
    pendingDuplicateId.value = duplicateId;
    mergeDialogOpen.value = true;
}

function merge(): void {
    if (pendingSurvivorId.value === null || pendingDuplicateId.value === null) {
        return;
    }

    mergeProcessing.value = true;

    router.post(
        contactsMerge.url({
            contact: pendingSurvivorId.value,
            duplicate: pendingDuplicateId.value,
        }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                mergeProcessing.value = false;
                mergeDialogOpen.value = false;
                pendingSurvivorId.value = null;
                pendingDuplicateId.value = null;
            },
        },
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-lg font-semibold text-gray-300">
                Possible duplicate contacts
            </h1>
            <p class="mt-1 text-sm text-gray-400">
                Review the pairs below and merge where they represent the same
                contact.
            </p>

            <div v-if="!candidates.length" class="mt-6 text-sm text-gray-400">
                No likely duplicates found.
            </div>

            <div v-else class="mt-6 space-y-4">
                <DuplicatePairCard
                    v-for="candidate in candidates"
                    :key="`${candidate.contact.id}-${candidate.duplicate.id}`"
                    :contact="candidate.contact"
                    :duplicate="candidate.duplicate"
                    :reason="candidate.reason"
                    :can-merge="permissions_meta.can_merge"
                    @merge="
                        requestMerge(
                            candidate.contact.id,
                            candidate.duplicate.id,
                        )
                    "
                />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="mergeDialogOpen"
            title="Merge contacts"
            description="The duplicate record will be merged into the survivor and moved to trash. This cannot be easily undone."
            confirm-label="Merge"
            :processing="mergeProcessing"
            @confirm="merge"
        />
    </div>
</template>
