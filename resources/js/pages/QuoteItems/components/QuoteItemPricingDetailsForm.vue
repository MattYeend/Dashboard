<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { QuoteItemFormData } from '@/types/forms';

defineProps<{
    errors: Partial<Record<keyof QuoteItemFormData, string>>;
    units: string[];
}>();

const form = defineModel<QuoteItemFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="item_quantity">Quantity</Label>
            <Input
                id="item_quantity"
                v-model="form.quantity"
                type="number"
                step="0.01"
                min="0.01"
                required
            />
            <InputError :message="errors.quantity" />
        </div>

        <div class="grid gap-2">
            <Label for="item_unit">Unit</Label>
            <select
                id="item_unit"
                v-model="form.unit"
                class="h-9 rounded-md border border-input px-3 text-sm"
            >
                <option v-for="unit in units" :key="unit" :value="unit">
                    {{ unit }}
                </option>
            </select>
            <InputError :message="errors.unit" />
        </div>

        <div class="grid gap-2">
            <Label for="item_unit_price">Unit price</Label>
            <Input
                id="item_unit_price"
                v-model="form.unit_price"
                type="number"
                step="0.01"
                min="0"
            />
            <InputError :message="errors.unit_price" />
        </div>

        <div class="grid gap-2">
            <Label for="item_discount_percent">Discount (%)</Label>
            <Input
                id="item_discount_percent"
                v-model="form.discount_percent"
                type="number"
                step="0.01"
                min="0"
                max="100"
            />
            <InputError :message="errors.discount_percent" />
        </div>

        <div class="grid gap-2">
            <Label for="item_tax_rate">Tax rate (%)</Label>
            <Input
                id="item_tax_rate"
                v-model="form.tax_rate"
                type="number"
                step="0.01"
                min="0"
                max="100"
            />
            <InputError :message="errors.tax_rate" />
        </div>
    </div>
</template>
