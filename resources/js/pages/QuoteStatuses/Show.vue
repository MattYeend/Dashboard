<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit, index } from '@/routes/quote-statuses';
import type { QuoteStatus } from '@/types';

import QuoteStatusAuditDetails from './components/QuoteStatusAuditDetails.vue';
import QuoteStatusBasicDetails from './components/QuoteStatusBasicDetails.vue';
import QuoteStatusColourDetails from './components/QuoteStatusColourDetails.vue';

defineProps<{
    quoteStatus: QuoteStatus;
}>();
</script>

<template>
    <Head :title="quoteStatus.title" />

    <AppLayout :breadcrumbs="[{ title: 'Quote statuses', href: index.url() }, { title: quoteStatus.title, href: '#' }]">
        <div class="mx-auto grid w-full max-w-3xl gap-8 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ quoteStatus.title }}</h1>
                <Button v-if="!quoteStatus.deleted_at" as-child>
                    <Link :href="edit.url(quoteStatus.id)">Edit</Link>
                </Button>
            </div>

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
    </AppLayout>
</template>