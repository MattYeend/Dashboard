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
    <div class="grid gap-2 border-b border-gray-500 pb-4">
        <Label for="published-at">Publish date (leave blank for draft)</Label>
        <Input
            id="published-at"
            v-model="publishedAt"
            type="datetime-local"
        />
        <InputError :message="errors.published_at" />
    </div>
</template>
