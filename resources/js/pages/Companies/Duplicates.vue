<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { merge as companiesMerge } from '@/routes/companies';
import DuplicatePairCard from './components/DuplicatePairCard.vue';

interface CompanyDuplicateSummary {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
}

interface CompanyDuplicateCandidate {
    company: CompanyDuplicateSummary;
    duplicate: CompanyDuplicateSummary;
    reason: string;
}

interface Props {
    candidates: CompanyDuplicateCandidate[];
    permissions_meta: {
        can_merge: boolean;
    };
}

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Companies', href: '/companies' },
            { title: 'Duplicates', href: '/companies/duplicates' },
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
        companiesMerge.url({
            company: pendingSurvivorId.value,
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
                Possible duplicate companies
            </h1>
            <p class="mt-1 text-sm text-gray-400">
                Review the pairs below and merge where they represent the same
                company.
            </p>

            <div v-if="!candidates.length" class="mt-6 text-sm text-gray-400">
                No likely duplicates found.
            </div>

            <div v-else class="mt-6 space-y-4">
                <DuplicatePairCard
                    v-for="candidate in candidates"
                    :key="`${candidate.company.id}-${candidate.duplicate.id}`"
                    :company="candidate.company"
                    :duplicate="candidate.duplicate"
                    :reason="candidate.reason"
                    :can-merge="permissions_meta.can_merge"
                    @merge="
                        requestMerge(
                            candidate.company.id,
                            candidate.duplicate.id,
                        )
                    "
                />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="mergeDialogOpen"
            title="Merge companies"
            description="The duplicate record will be merged into the survivor and moved to trash. This cannot be easily undone."
            confirm-label="Merge"
            :processing="mergeProcessing"
            @confirm="merge"
        />
    </div>
</template>
