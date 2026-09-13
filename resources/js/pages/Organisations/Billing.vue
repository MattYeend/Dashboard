<script setup lang="ts">
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
    <div class="py-6">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold">Billing</h1>

            <template v-if="props.plan">
                <BillingPlanDetails :plan="props.plan" />
                <BillingSeatSummary
                    :seats="props.seats"
                    :price-per-user-per-month="
                        props.plan.price_per_user_per_month
                    "
                    :total="props.total ?? 0"
                />
            </template>

            <p v-else class="text-sm text-gray-400">
                This organisation doesn't have an active subscription.
            </p>
        </div>
    </div>
</template>
