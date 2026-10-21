<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { SelectOption } from '@/types';

defineProps<{
    typeOptions: SelectOption[];
    sortFields: Record<string, string>;
    canViewTrashed: boolean;
}>();

const emit = defineEmits<{ apply: []; reset: [] }>();

const search = defineModel<string>('search', { required: true });
const type = defineModel<string>('type', { required: true });
const trashed = defineModel<string>('trashed', { required: true });
const sortBy = defineModel<string>('sortBy', { required: true });
const sortDirection = defineModel<string>('sortDirection', { required: true });
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
            placeholder="Search questions"
            class="w-64"
        />

        <select
            v-model="type"
            class="rounded-md border border-input px-3 py-2 text-sm"
            aria-label="Question type"
        >
            <option value="">All types</option>
            <option
                v-for="option in typeOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <select
            v-if="canViewTrashed"
            v-model="trashed"
            class="rounded-md border border-input px-3 py-2 text-sm"
            aria-label="Deleted questions"
        >
            <option value="">Active only</option>
            <option value="with">Include deleted</option>
            <option value="only">Deleted only</option>
        </select>

        <select
            v-model="sortBy"
            class="rounded-md border border-input px-3 py-2 text-sm"
            aria-label="Sort by"
        >
            <option v-for="(label, key) in sortFields" :key="key" :value="key">
                {{ label }}
            </option>
        </select>

        <select
            v-model="sortDirection"
            class="rounded-md border border-input px-3 py-2 text-sm"
            aria-label="Sort direction"
        >
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
        </select>

        <Button type="submit">Filter</Button>
        <Button type="button" variant="outline" @click="emit('reset')"
            >Reset</Button
        >
    </form>
</template>
