<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

defineProps<{
    errors: Partial<Record<string, string>>;
}>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const notes = defineModel<string | null>('notes', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title"
                >Title <span class="text-destructive">*</span></Label
            >
            <Input
                id="title"
                v-model="title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter order title"
            />
            <InputError :message="errors.title" />
        </div>

        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="description ?? ''"
                class="mt-1 block w-full"
                rows="3"
                placeholder="Enter order description"
                @update:model-value="description = ($event as string) || null"
            />
            <InputError :message="errors.description" />
        </div>

        <div>
            <Label for="notes">Notes</Label>
            <Textarea
                id="notes"
                :model-value="notes ?? ''"
                class="mt-1 block w-full"
                rows="3"
                placeholder="Enter internal notes"
                @update:model-value="notes = ($event as string) || null"
            />
            <InputError :message="errors.notes" />
        </div>
    </div>
</template>
