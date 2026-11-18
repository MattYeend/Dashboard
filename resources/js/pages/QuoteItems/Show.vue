<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import QuoteItemAuditDetails from '@/pages/QuoteItems/components/QuoteItemAuditDetails.vue';
import QuoteItemBasicDetails from '@/pages/QuoteItems/components/QuoteItemBasicDetails.vue';
import QuoteItemPricingDetails from '@/pages/QuoteItems/components/QuoteItemPricingDetails.vue';
import {
    destroy as quoteItemsDestroy,
    edit as quoteItemsEdit,
    index as quoteItemsIndex,
} from '@/routes/quotes/items';
import type { Quote, QuoteItem } from '@/types';

interface Props {
    quote: Quote;
    item: QuoteItem;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.item?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.item?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(
        quoteItemsDestroy.url({
            quote: props.quote.id,
            quoteItem: props.item.id,
        }),
        {
            onFinish: () => {
                deleteProcessing.value = false;
                deleteDialogOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="`${item.name} - ${quote.quote_number}`" />

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ item.name }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="quoteItemsIndex.url({ quote: quote.id })"
                            >Back</Link
                        >
                    </Button>
                    <Button as-child>
                        <Link
                            :href="
                                quoteItemsEdit.url({
                                    quote: quote.id,
                                    quoteItem: item.id,
                                })
                            "
                            >Edit</Link
                        >
                    </Button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <QuoteItemBasicDetails :item="item" />
                <QuoteItemPricingDetails
                    :item="item"
                    :currency="quote.currency"
                />
                <QuoteItemAuditDetails :item="item" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete quote item"
            description="This quote item will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
