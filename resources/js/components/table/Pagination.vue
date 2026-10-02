<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Pagination, PaginationLink } from '@/types';

interface Props {
    meta: Pagination;
    links: PaginationLink[];
    resourceLabel: string;
}

defineProps<Props>();

const labelEntities: Record<string, string> = {
    '&laquo;': '«',
    '&raquo;': '»',
};

const formatLabel = (label: string): string => {
    return label.replace(/&laquo;|&raquo;/g, (entity) => labelEntities[entity]);
};
</script>

<template>
    <div
        v-if="meta.last_page > 1"
        class="mt-4 flex items-center justify-between"
    >
        <p class="text-sm text-gray-400">
            Showing {{ meta.from ?? 0 }} to {{ meta.to ?? 0 }} of
            {{ meta.total }} {{ resourceLabel }}
        </p>
        <div class="flex gap-x-1">
            <Link
                v-for="(link, index) in links"
                :key="`${index}-${link.label}`"
                :href="link.url ?? ''"
                :class="[
                    'rounded px-3 py-1 text-sm',
                    link.url === null
                        ? 'pointer-events-none opacity-40'
                        : 'hover:underline',
                    link.active ? 'font-semibold' : '',
                ]"
                preserve-scroll
            >
                <span>{{ formatLabel(link.label) }}</span>
            </Link>
        </div>
    </div>
</template>
