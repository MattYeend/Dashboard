<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BillingPlanDetails from './components/BillingPlanDetails.vue';
import BillingSeatSummary from './components/BillingSeatSummary.vue';

interface BillingPlan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_per_user_per_month: number;
    is_active: boolean;
}

interface BillingOrganisation {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    organisation: BillingOrganisation;
    plan: BillingPlan | null;
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
