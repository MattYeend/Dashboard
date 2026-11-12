<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';

import QuoteAmountsForm from './QuoteAmountsForm.vue';
import QuoteBasicDetailsForm from './QuoteBasicDetailsForm.vue';
import QuoteCustomerDetailsForm from './QuoteCustomerDetailsForm.vue';
import QuoteDateDetailsForm from './QuoteDateDetailsForm.vue';
import type { QuoteFormData, SelectOption } from '@/types/forms/quotes';

defineProps<{
    errors: Partial<Record<keyof QuoteFormData, string>>;
    processing: boolean;
    quotableTypes: SelectOption[];
    currencies: string[];
    submitLabel: string;
    cancelHref: string;
}>();

const emit = defineEmits<{
    (event: 'submit'): void;
}>();

const form = defineModel<QuoteFormData>({ required: true });
</script>

<template>
    <form class="grid gap-8" @submit.prevent="emit('submit')">
        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Customer</h2>
            <QuoteCustomerDetailsForm
                v-model="form"
                :errors="errors"
                :quotable-types="quotableTypes"
            />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Basic details</h2>
            <QuoteBasicDetailsForm v-model="form" :errors="errors" />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Amounts</h2>
            <QuoteAmountsForm
                v-model="form"
                :errors="errors"
                :currencies="currencies"
            />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Dates</h2>
            <QuoteDateDetailsForm v-model="form" :errors="errors" />
        </section>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">{{
                submitLabel
            }}</Button>
            <Button as-child variant="outline">
                <Link :href="cancelHref">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
