<script setup lang="ts">
import { computed } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import type { QuoteFormData } from '@/types/forms';

defineProps<{
    errors: Partial<Record<keyof QuoteFormData, string>>;
    currencies: string[];
}>();

const form = defineModel<QuoteFormData>({ required: true });

function toPence(value: string): number {
    const parsed = Number.parseFloat(value);

    return Number.isFinite(parsed) ? Math.round(parsed * 100) : 0;
}

const previewTotal = computed(() => {
    const pence =
        toPence(form.value.subtotal) -
        toPence(form.value.discount_amount) +
        toPence(form.value.tax_amount);

    return (Math.max(pence, 0) / 100).toFixed(2);
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="subtotal">Subtotal</Label>
            <Input
                id="subtotal"
                v-model="form.subtotal"
                type="number"
                step="0.01"
                min="0"
                required
            />
            <p v-if="errors.subtotal" class="text-sm text-destructive">
                {{ errors.subtotal }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="discount_amount">Discount</Label>
            <Input
                id="discount_amount"
                v-model="form.discount_amount"
                type="number"
                step="0.01"
                min="0"
            />
            <p v-if="errors.discount_amount" class="text-sm text-destructive">
                {{ errors.discount_amount }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="tax_amount">Tax</Label>
            <Input
                id="tax_amount"
                v-model="form.tax_amount"
                type="number"
                step="0.01"
                min="0"
            />
            <p v-if="errors.tax_amount" class="text-sm text-destructive">
                {{ errors.tax_amount }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="currency">Currency</Label>
            <select
                id="currency"
                v-model="form.currency"
                class="h-9 rounded-md border border-input px-3 text-sm"
                required
            >
                <option
                    v-for="currency in currencies"
                    :key="currency"
                    :value="currency"
                >
                    {{ currency }}
                </option>
            </select>
            <p v-if="errors.currency" class="text-sm text-destructive">
                {{ errors.currency }}
            </p>
        </div>

        <p class="text-sm text-muted-foreground sm:col-span-2">
            Total preview: {{ form.currency }} {{ previewTotal }}. The final
            total is calculated when you save.
        </p>
    </div>
</template>
