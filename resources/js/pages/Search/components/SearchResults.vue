<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { SearchResults as SearchResultsType } from '@/types';

const props = defineProps<{
    results: SearchResultsType;
    loading: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const groups = computed(() =>
    [
        { key: 'users', label: 'Users', items: props.results.users },
        {
            key: 'companies',
            label: 'Companies',
            items: props.results.companies,
        },
        { key: 'contacts', label: 'Contacts', items: props.results.contacts },
        { key: 'orders', label: 'Orders', items: props.results.orders },
        { key: 'deals', label: 'Deals', items: props.results.deals },
    ].filter((group) => group.items.length > 0),
);

const flatItems = computed(() => groups.value.flatMap((group) => group.items));

const activeIndex = ref(-1);

const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex.value = Math.min(
            activeIndex.value + 1,
            flatItems.value.length - 1,
        );
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
    } else if (event.key === 'Enter' && activeIndex.value >= 0) {
        const item = flatItems.value[activeIndex.value];

        if (item) {
            router.visit(item.url);
        }
    } else if (event.key === 'Escape') {
        emit('close');
    }
};

defineExpose({ onKeydown });
</script>

<template>
    <div
        class="absolute z-50 mt-1 w-full rounded border border-gray-500"
        tabindex="-1"
        @keydown="onKeydown"
    >
        <p v-if="loading" class="p-3 text-sm text-gray-400">Searching...</p>

        <template v-else-if="groups.length">
            <div
                v-for="group in groups"
                :key="group.key"
                class="border-b border-gray-500 last:border-b-0"
            >
                <p class="px-3 pt-2 text-xs text-gray-400 uppercase">
                    {{ group.label }}
                </p>
                <a
                    v-for="item in group.items"
                    :key="`${group.key}-${item.id}`"
                    :href="item.url"
                    class="block px-3 py-2 text-sm"
                    @click.prevent="router.visit(item.url)"
                >
                    {{ item.label }}
                </a>
            </div>
        </template>

        <p v-else class="p-3 text-sm text-gray-400">No results found.</p>
    </div>
</template>
