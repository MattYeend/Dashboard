<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import type { ProductFormData } from '@/types/forms/products';

defineProps<{
    errors: Partial<Record<keyof ProductFormData, string>>;
}>();

const form = defineModel<ProductFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="sku">SKU</Label>
            <Input
                id="sku"
                v-model="form.sku"
                maxlength="64"
                autocomplete="off"
                required
            />
            <p v-if="errors.sku" class="text-sm text-destructive">
                {{ errors.sku }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" maxlength="255" required />
            <p v-if="errors.name" class="text-sm text-destructive">
                {{ errors.name }}
            </p>
        </div>

        <div class="grid gap-2 sm:col-span-2">
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                v-model="form.description as string"
                rows="4"
                maxlength="5000"
            />
            <p v-if="errors.description" class="text-sm text-destructive">
                {{ errors.description }}
            </p>
        </div>
    </div>
</template>