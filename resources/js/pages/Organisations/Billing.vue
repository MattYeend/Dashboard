<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { Organisation, Plan } from '@/types';
import BillingPlanDetails from './components/BillingPlanDetails.vue';
import BillingSeatSummary from './components/BillingSeatSummary.vue';

const props = defineProps<{
    organisation: Organisation;
    plan: Plan | null;
    seats: number;
    total: number | null;
}>();
</script>

<template>
    <Head :title="`${props.organisation.name} — Billing`" />

    <div>
        <h1>Billing</h1>

        <template v-if="props.plan">
            <BillingPlanDetails :plan="props.plan" />
            <BillingSeatSummary
                :seats="props.seats"
                :price-per-user-per-month="props.plan.price_per_user_per_month"
                :total="props.total ?? 0"
            />
        </template>

        <p v-else>This organisation doesn't have an active subscription.</p>
    </div>
</template>
