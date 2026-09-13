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
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Seats</h2>

        <dl class="space-y-4">
            <div>
                <dt class="text-xs font-medium text-gray-400">Active seats</dt>
                <dd class="mt-1 text-sm text-gray-300">{{ props.seats }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400">
                    Price per seat
                </dt>
                <dd class="mt-1 text-sm text-gray-300">
                    {{ formattedPricePerSeat }} / month
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-400">Total</dt>
                <dd class="mt-1 text-sm text-gray-300">
                    {{ formattedTotal }} / month
                </dd>
            </div>
        </dl>
    </div>
</template>
