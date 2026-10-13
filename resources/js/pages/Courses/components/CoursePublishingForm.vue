<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<string, string>>;
}>();

const publishedAt = defineModel<string>('publishedAt', { required: true });
</script>

<template>
    <div class="grid gap-2">
        <Label for="published-at">Publish at</Label>
        <Input id="published-at" v-model="publishedAt" type="datetime-local" />
        <p class="text-xs text-gray-400">
            Leave blank to keep this course as a draft.
        </p>
        <InputError :message="errors.published_at" />
    </div>
</template>
