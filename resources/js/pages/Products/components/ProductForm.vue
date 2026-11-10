<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import type { ProductFormData } from '@/types/forms/products';

import ProductBasicDetailsForm from './ProductBasicDetailsForm.vue';
import ProductPricingForm from './ProductPricingForm.vue';
import ProductStatusForm from './ProductStatusForm.vue';

defineProps<{
    errors: Partial<Record<keyof ProductFormData, string>>;
    processing: boolean;
    units: string[];
    currencies: string[];
    submitLabel: string;
    cancelHref: string;
}>();

const emit = defineEmits<{
    (event: 'submit'): void;
}>();

const form = defineModel<ProductFormData>({ required: true });
</script>

<template>
    <form class="grid gap-8" @submit.prevent="emit('submit')">
        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Basic details</h2>
            <ProductBasicDetailsForm v-model="form" :errors="errors" />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Pricing</h2>
            <ProductPricingForm
                v-model="form"
                :errors="errors"
                :units="units"
                :currencies="currencies"
            />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Availability</h2>
            <ProductStatusForm v-model="form" :errors="errors" />
        </section>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>

            <Button as-child variant="outline">
                <Link :href="cancelHref">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
