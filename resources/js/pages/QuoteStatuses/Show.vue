<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import QuoteStatusAuditDetails from '@/pages/QuoteStatuses/components/QuoteStatusAuditDetails.vue';
import QuoteStatusBasicDetails from '@/pages/QuoteStatuses/components/QuoteStatusBasicDetails.vue';
import QuoteStatusColourDetails from '@/pages/QuoteStatuses/components/QuoteStatusColourDetails.vue';
import {
    destroy as quoteStatusesDestroy,
    edit as quoteStatusesEdit,
    index as quoteStatusesIndex,
} from '@/routes/quote-statuses';
import type { QuoteStatus } from '@/types';

const props = defineProps<{
    quoteStatus: QuoteStatus;
}>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(quoteStatusesDestroy.url(props.quoteStatus.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <Head :title="quoteStatus.title" />

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ quoteStatus.title }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="quoteStatusesIndex.url()">Back</Link>
                    </Button>
                    <template v-if="!quoteStatus.deleted_at">
                        <Button as-child>
                            <Link :href="quoteStatusesEdit.url(quoteStatus.id)"
                                >Edit</Link
                            >
                        </Button>
                        <button
                            v-if="!quoteStatus.is_system"
                            type="button"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                            @click="requestDestroy"
                        >
                            Delete
                        </button>
                    </template>
                </div>
            </div>

            <div class="space-y-6">
                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Basic details</h2>
                    <QuoteStatusBasicDetails :quote-status="quoteStatus" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Colours</h2>
                    <QuoteStatusColourDetails :quote-status="quoteStatus" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Audit</h2>
                    <QuoteStatusAuditDetails :quote-status="quoteStatus" />
                </section>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete quote status"
            description="This quote status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
