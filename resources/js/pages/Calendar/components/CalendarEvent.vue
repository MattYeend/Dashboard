<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { CalendarEvent } from '@/types';

interface Props {
    event: CalendarEvent;
}

const props = defineProps<Props>();

const handleClick = (): void => {
    router.visit(props.event.show_url);
};
</script>

<template>
    <button
        @click="handleClick"
        class="mb-1 block w-full truncate rounded px-1 py-0.5 text-left text-xs"
        :style="{
            backgroundColor: event.background_colour ?? undefined,
            color: event.text_colour ?? undefined,
        }"
    >
        {{ event.title }}
    </button>
</template>
