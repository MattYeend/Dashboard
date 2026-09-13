<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    seats: number;
    pricePerUserPerMonth: number;
    total: number;
}>();

const formattedPricePerSeat = computed(() =>
    (props.pricePerUserPerMonth / 100).toFixed(2),
);
const formattedTotal = computed(() => (props.total / 100).toFixed(2));
</script>

<template>
    <section>
        <h2>Seats</h2>
        <dl>
            <dt>Active seats</dt>
            <dd>{{ props.seats }}</dd>

            <dt>Price per seat</dt>
            <dd>{{ formattedPricePerSeat }} / month</dd>

            <dt>Total</dt>
            <dd>{{ formattedTotal }} / month</dd>
        </dl>
    </section>
</template>
