<script setup lang="ts">
import { computed } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ProductFormData } from '@/types/forms/products';

defineProps<{
    errors: Partial<Record<keyof ProductFormData, string>>;
    units: string[];
    currencies: string[];
}>();

const form = defineModel<ProductFormData>({ required: true });

const costPrice = computed<string>({
    get: () => form.value.cost_price ?? '',
    set: (value: string) => {
        form.value.cost_price = value === '' ? null : value;
    },
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="unit_price">Unit price</Label>
            <Input
                id="unit_price"
                v-model="form.unit_price"
                type="number"
                step="0.01"
                min="0"
                max="99999999.99"
                required
            />
            <p v-if="errors.unit_price" class="text-sm text-destructive">
                {{ errors.unit_price }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="cost_price">Cost price</Label>
            <Input
                id="cost_price"
                v-model="costPrice"
                type="number"
                step="0.01"
                min="0"
                max="99999999.99"
            />
            <p v-if="errors.cost_price" class="text-sm text-destructive">
                {{ errors.cost_price }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="tax_rate">Tax rate (%)</Label>
            <Input
                id="tax_rate"
                v-model="form.tax_rate"
                type="number"
                step="0.01"
                min="0"
                max="100"
                required
            />
            <p v-if="errors.tax_rate" class="text-sm text-destructive">
                {{ errors.tax_rate }}
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
                <option v-for="currency in currencies" :key="currency" :value="currency">
                    {{ currency }}
                </option>
            </select>
            <p v-if="errors.currency" class="text-sm text-destructive">
                {{ errors.currency }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="unit">Unit</Label>
            <select
                id="unit"
                v-model="form.unit"
                class="h-9 rounded-md border border-input px-3 text-sm"
                required
            >
                <option v-for="unit in units" :key="unit" :value="unit">
                    {{ unit }}
                </option>
            </select>
            <p v-if="errors.unit" class="text-sm text-destructive">
                {{ errors.unit }}
            </p>
        </div>
    </div>
</template>