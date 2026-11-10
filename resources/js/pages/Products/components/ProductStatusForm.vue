<script setup lang="ts">
import { Label } from '@/components/ui/label';

import type { ProductFormData } from '@/types/forms/products';

defineProps<{
    errors: Partial<Record<keyof ProductFormData, string>>;
}>();

const form = defineModel<ProductFormData>({ required: true });
</script>

<template>
    <div class="flex items-center gap-2">
        <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="size-4 rounded border border-input"
        />
        <Label for="is_active">Active (available to add to quotes)</Label>
        <p v-if="errors.is_active" class="text-sm text-destructive">
            {{ errors.is_active }}
        </p>
    </div>
</template>
