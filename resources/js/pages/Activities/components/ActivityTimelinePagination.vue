<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Pagination } from '@/types';

defineProps<{
    meta: Pagination;
}>();

const emit = defineEmits<{
    change: [page: number];
}>();
</script>

<template>
    <div class="mt-4 flex items-center justify-between text-sm text-gray-400">
        <span>{{ meta.from }}-{{ meta.to }} of {{ meta.total }}</span>
        <div class="flex gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="meta.current_page <= 1"
                @click="emit('change', meta.current_page - 1)"
            >
                Previous
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="meta.current_page >= meta.last_page"
                @click="emit('change', meta.current_page + 1)"
            >
                Next
            </Button>
        </div>
    </div>
</template>
