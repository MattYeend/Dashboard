<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import QuoteItemBasicDetailsForm from '@/pages/QuoteItems/components/QuoteItemBasicDetailsForm.vue';
import QuoteItemPricingDetailsForm from '@/pages/QuoteItems/components/QuoteItemPricingDetailsForm.vue';
import { index as quoteItemsIndex } from '@/routes/quotes/items';
import type { ProductOption, QuoteItemFormData } from '@/types/forms';

defineProps<{
    quoteId: number;
    isEditing: boolean;
    processing: boolean;
    errors: Partial<Record<keyof QuoteItemFormData, string>>;
    products: ProductOption[];
    units: string[];
}>();

const emit = defineEmits<{
    (event: 'submit'): void;
}>();

const form = defineModel<QuoteItemFormData>({ required: true });
</script>

<template>
    <form class="grid gap-8" @submit.prevent="emit('submit')">
        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Basic details</h2>
            <QuoteItemBasicDetailsForm
                v-model="form"
                :errors="errors"
                :products="products"
            />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Pricing</h2>
            <QuoteItemPricingDetailsForm
                v-model="form"
                :errors="errors"
                :units="units"
            />
        </section>

        <div class="flex items-center justify-end gap-3">
            <Button as-child variant="outline">
                <Link :href="quoteItemsIndex.url({ quote: quoteId })">
                    Cancel
                </Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update item' : 'Create item' }}
            </Button>
        </div>
    </form>
</template>
