<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineProps<{
    canViewTrashed: boolean;
    canViewCorrect: boolean;
}>();

const emit = defineEmits<{ apply: []; reset: [] }>();

const search = defineModel<string>('search', { required: true });
const isCorrect = defineModel<string>('isCorrect', { required: true });
const trashed = defineModel<string>('trashed', { required: true });
</script>

<template>
    <form
        class="flex flex-wrap items-end gap-3"
        @submit.prevent="emit('apply')"
    >
        <Input
            v-model="search"
            type="search"
            maxlength="100"
            placeholder="Search answers"
            class="w-64"
        />

        <select
            v-if="canViewCorrect"
            v-model="isCorrect"
            class="rounded-md border border-input px-3 py-2 text-sm"
        >
            <option value="">Any correctness</option>
            <option value="1">Correct only</option>
            <option value="0">Incorrect only</option>
        </select>

        <select
            v-if="canViewTrashed"
            v-model="trashed"
            class="rounded-md border border-input px-3 py-2 text-sm"
        >
            <option value="">Active only</option>
            <option value="with">Include deleted</option>
            <option value="only">Deleted only</option>
        </select>

        <Button type="submit">Filter</Button>
        <Button type="button" variant="outline" @click="emit('reset')">
            Reset
        </Button>
    </form>
</template>
