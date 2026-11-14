<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import type { QuoteStatusFormData } from '@/types/forms';

defineProps<{
    errors: Partial<Record<keyof QuoteStatusFormData, string>>;
    isSystem: boolean;
}>();

const form = defineModel<QuoteStatusFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4">
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input
                id="title"
                v-model="form.title"
                maxlength="255"
                :disabled="isSystem"
                required
            />
            <p v-if="isSystem" class="text-sm text-muted-foreground">
                System statuses cannot be renamed.
            </p>
            <p v-if="errors.title" class="text-sm text-destructive">
                {{ errors.title }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                v-model="form.description as string"
                rows="3"
                maxlength="1000"
            />
            <p v-if="errors.description" class="text-sm text-destructive">
                {{ errors.description }}
            </p>
        </div>
    </div>
</template>
