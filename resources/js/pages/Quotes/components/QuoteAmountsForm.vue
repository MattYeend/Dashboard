<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import type { QuoteFormData } from '@/types/forms';

defineProps<{
    errors: Partial<Record<keyof QuoteFormData, string>>;
    currencies: string[];
    showDiscount: boolean;
}>();

const form = defineModel<QuoteFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div v-if="showDiscount" class="grid gap-2">
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
            Subtotal and tax are calculated from the quote lines.
        </p>
    </div>
</template>
