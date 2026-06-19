<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Pagination } from '@/types';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    meta: Pagination;
    links: PaginationLink[];
    resourceLabel: string;
}

defineProps<Props>();
</script>

<template>
    <div
        v-if="meta.last_page > 1"
        class="mt-4 flex items-center justify-between"
    >
        <p class="text-grey-500 text-sm">
            Showing {{ meta.from ?? 0 }} to {{ meta.to ?? 0 }} of
            {{ meta.total }} {{ resourceLabel }}
        </p>
        <div class="flex gap-x-1">
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.url ?? ''"
                :class="[
                    'rounded px-3 py-1 text-sm',
                    link.url === null
                        ? 'pointer-events-none opacity-40'
                        : 'hover:bg-accent',
                    link.active ? 'font-semibold' : '',
                ]"
                preserve-scroll
            >
                <span v-html="link.label" />
            </Link>
        </div>
    </div>
</template>
