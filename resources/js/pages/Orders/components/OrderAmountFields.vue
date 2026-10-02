<script setup lang="ts">
import CurrencySelect from '@/components/CurrencySelect.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<string, string>>;
}>();

const subtotal = defineModel<number>('subtotal', { required: true });
const discountAmount = defineModel<number>('discountAmount', {
    required: true,
});
const taxAmount = defineModel<number>('taxAmount', { required: true });
const totalAmount = defineModel<number>('totalAmount', { required: true });
const currency = defineModel<string>('currency', { required: true });
</script>

<template>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <Label for="subtotal">Subtotal</Label>
            <Input
                id="subtotal"
                v-model.number="subtotal"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.subtotal" />
        </div>
        <div>
            <Label for="discount_amount">Discount</Label>
            <Input
                id="discount_amount"
                v-model.number="discountAmount"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.discount_amount" />
        </div>
        <div>
            <Label for="tax_amount">Tax</Label>
            <Input
                id="tax_amount"
                v-model.number="taxAmount"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.tax_amount" />
        </div>
        <div>
            <Label for="total_amount">Total</Label>
            <Input
                id="total_amount"
                v-model.number="totalAmount"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.total_amount" />
        </div>
        <div>
            <Label for="currency">Currency</Label>
            <CurrencySelect v-model="currency" class="mt-1" />
            <InputError :message="errors.currency" />
        </div>
    </div>
</template>
