<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import type { QuoteFormData } from '@/types/forms/quotes';

defineProps<{
    errors: Partial<Record<keyof QuoteFormData, string>>;
}>();

const form = defineModel<QuoteFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4">
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input id="title" v-model="form.title" maxlength="255" required />
            <p v-if="errors.title" class="text-sm text-destructive">
                {{ errors.title }}
            </p>
        </div>

        <div class="grid gap-2">
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

        <div class="grid gap-2">
            <Label for="notes">Internal notes</Label>
            <Textarea
                id="notes"
                v-model="form.notes as string"
                rows="3"
                maxlength="5000"
            />
            <p v-if="errors.notes" class="text-sm text-destructive">
                {{ errors.notes }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="terms">Terms</Label>
            <Textarea
                id="terms"
                v-model="form.terms as string"
                rows="3"
                maxlength="10000"
            />
            <p v-if="errors.terms" class="text-sm text-destructive">
                {{ errors.terms }}
            </p>
        </div>
    </div>
</template>
