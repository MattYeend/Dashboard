<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        showHref: string;
        editHref?: string;
        trashed?: boolean;
    }>(),
    { editHref: undefined, trashed: false },
);

const emit = defineEmits<{
    delete: [];
    restore: [];
    forceDelete: [];
}>();
</script>

<template>
    <Link :href="showHref">View</Link>

    <template v-if="!trashed">
        <Link v-if="editHref" :href="editHref">Edit</Link>
        <slot />
        <button
            type="button"
            class="text-red-600 hover:text-red-900"
            @click="emit('delete')"
        >
            Delete
        </button>
    </template>

    <template v-else>
        <button type="button" @click="emit('restore')">Restore</button>
        <button
            type="button"
            class="text-red-600 hover:text-red-900"
            @click="emit('forceDelete')"
        >
            Delete permanently
        </button>
    </template>
</template>
