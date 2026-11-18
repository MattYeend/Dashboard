<script setup lang="ts">
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { ProductOption, QuoteItemFormData } from '@/types/forms';

const props = defineProps<{
    errors: Partial<Record<keyof QuoteItemFormData, string>>;
    products: ProductOption[];
}>();

const form = defineModel<QuoteItemFormData>({ required: true });

watch(
    () => form.value.product_id,
    (productId) => {
        const product = props.products.find((item) => item.id === productId);

        if (!product) {
            return;
        }

        form.value.name = product.name;
        form.value.description = product.description;
        form.value.unit = product.unit;
        form.value.unit_price = product.unit_price;
        form.value.tax_rate = product.tax_rate;
    },
);
</script>

<template>
    <div class="grid gap-4">
        <div class="grid gap-2">
            <Label for="item_product_id">Product</Label>
            <select
                id="item_product_id"
                v-model="form.product_id"
                class="h-9 rounded-md border border-input px-3 text-sm"
            >
                <option :value="null">Custom line (no product)</option>
                <option
                    v-for="product in products"
                    :key="product.id"
                    :value="product.id"
                >
                    {{ product.sku }} - {{ product.name }}
                </option>
            </select>
            <InputError :message="errors.product_id" />
        </div>

        <div class="grid gap-2">
            <Label for="item_name">Name</Label>
            <Input id="item_name" v-model="form.name" maxlength="255" />
            <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="item_description">Description</Label>
            <Textarea
                id="item_description"
                :model-value="form.description ?? ''"
                rows="2"
                maxlength="5000"
                @update:model-value="
                    form.description = $event === '' ? null : String($event)
                "
            />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>