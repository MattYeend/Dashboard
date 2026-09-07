<script setup lang="ts">
import { ref, watch } from 'vue';
import type { UserOption } from '@/types';

interface Props {
    query: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    select: [user: UserOption];
}>();

const results = ref<UserOption[]>([]);
const loading = ref(false);

let debounceTimer: ReturnType<typeof setTimeout> | undefined;

watch(
    () => props.query,
    (value) => {
        clearTimeout(debounceTimer);

        if (value.length === 0) {
            results.value = [];

            return;
        }

        debounceTimer = setTimeout(() => fetchUsers(value), 200);
    },
);

async function fetchUsers(search: string): Promise<void> {
    loading.value = true;

    try {
        const response = await fetch(
            `/users/mentionable?search=${encodeURIComponent(search)}`,
            {
                headers: { Accept: 'application/json' },
            },
        );

        results.value = await response.json();
    } finally {
        loading.value = false;
    }
}

function choose(user: UserOption): void {
    emit('select', user);
    results.value = [];
}
</script>

<template>
    <ul
        v-if="results.length > 0"
        class="absolute z-10 mt-1 max-h-48 w-56 overflow-y-auto rounded-md border border-gray-600 bg-gray-800 text-sm text-gray-300"
    >
        <li
            v-for="user in results"
            :key="user.id"
            class="cursor-pointer px-3 py-1 hover:bg-gray-700"
            @click="choose(user)"
        >
            {{ user.name }}
        </li>
    </ul>
</template>
