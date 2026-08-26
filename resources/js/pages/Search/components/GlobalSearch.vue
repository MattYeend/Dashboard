<script setup lang="ts">
import axios from 'axios';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Input } from '@/components/ui/input';
import SearchResults from './SearchResults.vue';
import type { SearchResults as SearchResultsType } from '@/types';

const term = ref('');
const results = ref<SearchResultsType | null>(null);
const isOpen = ref(false);
const isLoading = ref(false);
const wrapper = ref<HTMLElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const runSearch = async (value: string) => {
    if (value.trim().length < 2) {
        results.value = null;
        isOpen.value = false;

        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.get<SearchResultsType>('/search', {
            params: { q: value.trim() },
        });

        results.value = response.data;
        isOpen.value = true;
    } finally {
        isLoading.value = false;
    }
};

watch(term, (value) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => runSearch(value), 300);
});

const closeResults = () => {
    isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    if (wrapper.value && !wrapper.value.contains(event.target as Node)) {
        closeResults();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);

    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
});
</script>

<template>
    <div ref="wrapper" class="relative z-50 w-full max-w-md">
        <Input
            v-model="term"
            type="search"
            placeholder="Search users, companies, contacts, orders, deals..."
            @focus="
                () => {
                    if (results) isOpen = true;
                }
            "
        />

        <SearchResults
            v-if="isOpen && results"
            :results="results"
            :loading="isLoading"
            @close="closeResults"
        />
    </div>
</template>
