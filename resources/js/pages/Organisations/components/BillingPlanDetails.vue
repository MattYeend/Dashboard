<script setup lang="ts">
interface Plan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_per_user_per_month: number;
    is_active: boolean;
}

defineProps<{
    plan: Plan;
}>();
</script>

<template>
    <section>
        <h2>Current plan</h2>
        <dl>
            <dt>Plan</dt>
            <dd>{{ plan.name }}</dd>

            <template v-if="plan.description">
                <dt>Description</dt>
                <dd>{{ plan.description }}</dd>
            </template>

            <dt>Price per seat</dt>
            <dd>
                {{ (plan.price_per_user_per_month / 100).toFixed(2) }} per user
                / month
            </dd>
        </dl>
    </section>
</template>
