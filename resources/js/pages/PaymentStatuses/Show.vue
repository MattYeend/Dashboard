<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit, index } from '@/routes/payment-statuses';
import type { PaymentStatus } from '@/types';

import PaymentStatusAuditDetails from './components/PaymentStatusAuditDetails.vue';
import PaymentStatusBasicDetails from './components/PaymentStatusBasicDetails.vue';
import PaymentStatusColourDetails from './components/PaymentStatusColourDetails.vue';

defineProps<{
    paymentStatus: PaymentStatus;
}>();
</script>

<template>
    <Head :title="paymentStatus.title" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Payment statuses', href: index.url() },
            { title: paymentStatus.title, href: '#' },
        ]"
    >
        <div class="mx-auto grid w-full max-w-3xl gap-8 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ paymentStatus.title }}
                </h1>
                <Button v-if="!paymentStatus.deleted_at" as-child>
                    <Link :href="edit.url(paymentStatus.id)">Edit</Link>
                </Button>
            </div>

            <section class="grid gap-4">
                <h2 class="text-lg font-semibold">Basic details</h2>
                <PaymentStatusBasicDetails :payment-status="paymentStatus" />
            </section>

            <section class="grid gap-4">
                <h2 class="text-lg font-semibold">Colours</h2>
                <PaymentStatusColourDetails :payment-status="paymentStatus" />
            </section>

            <section class="grid gap-4">
                <h2 class="text-lg font-semibold">Audit</h2>
                <PaymentStatusAuditDetails :payment-status="paymentStatus" />
            </section>
        </div>
    </AppLayout>
</template>
