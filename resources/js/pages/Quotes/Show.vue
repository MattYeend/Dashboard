<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import QuoteActions from '@/pages/Quotes/components/QuoteActions.vue';
import QuoteAmountDetails from '@/pages/Quotes/components/QuoteAmountDetails.vue';
import QuoteAuditDetails from '@/pages/Quotes/components/QuoteAuditDetails.vue';
import QuoteBasicDetails from '@/pages/Quotes/components/QuoteBasicDetails.vue';
import QuoteCustomerDetails from '@/pages/Quotes/components/QuoteCustomerDetails.vue';
import QuoteDateDetails from '@/pages/Quotes/components/QuoteDateDetails.vue';
import { index as quotesIndex } from '@/routes/quotes';
import type { Quote } from '@/types';
import type { QuoteAbilities } from '@/types/forms/quotes-form-data';

defineProps<{
    quote: Quote;
    abilities: QuoteAbilities;
}>();
</script>

<template>
    <Head :title="quote.quote_number" />

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ quote.quote_number }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="quotesIndex.url()">Back</Link>
                    </Button>
                    <QuoteActions :quote="quote" :abilities="abilities" />
                </div>
            </div>

            <div class="space-y-6">
                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Customer</h2>
                    <QuoteCustomerDetails :quote="quote" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Basic details</h2>
                    <QuoteBasicDetails :quote="quote" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Amounts</h2>
                    <QuoteAmountDetails :quote="quote" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Dates</h2>
                    <QuoteDateDetails :quote="quote" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Audit</h2>
                    <QuoteAuditDetails :quote="quote" />
                </section>
            </div>
        </div>
    </div>
</template>
