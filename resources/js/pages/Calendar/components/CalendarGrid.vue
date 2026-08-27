<script setup lang="ts">
import { computed } from 'vue';
import CalendarEvent from './CalendarEvent.vue';
import type { CalendarEvent as CalendarEventType } from '@/types';

interface Props {
    viewMode: 'month' | 'week';
    currentDate: Date;
    events: CalendarEventType[];
    isLoading: boolean;
}

const props = defineProps<Props>();

const toDateKey = (date: Date): string => date.toISOString().split('T')[0];

const days = computed<Date[]>(() => {
    const start = new Date(props.currentDate);

    if (props.viewMode === 'month') {
        start.setDate(1);
        start.setDate(start.getDate() - start.getDay());

        return Array.from({ length: 42 }, (_, index) => {
            const day = new Date(start);
            day.setDate(start.getDate() + index);

            return day;
        });
    }

    start.setDate(start.getDate() - start.getDay());

    return Array.from({ length: 7 }, (_, index) => {
        const day = new Date(start);
        day.setDate(start.getDate() + index);

        return day;
    });
});

const eventsByDate = computed<Record<string, CalendarEventType[]>>(() => {
    return props.events.reduce(
        (accumulator: Record<string, CalendarEventType[]>, event) => {
            accumulator[event.start] = accumulator[event.start] ?? [];
            accumulator[event.start].push(event);

            return accumulator;
        },
        {},
    );
});

const isCurrentMonth = (date: Date): boolean =>
    date.getMonth() === props.currentDate.getMonth();
</script>

<template>
    <div class="grid grid-cols-7 gap-px border border-gray-500">
        <div
            v-for="day in days"
            :key="toDateKey(day)"
            class="min-h-[6rem] border border-gray-500 p-2"
            :class="{
                'opacity-50': viewMode === 'month' && !isCurrentMonth(day),
            }"
        >
            <div class="mb-1 text-xs text-gray-400">{{ day.getDate() }}</div>
            <CalendarEvent
                v-for="event in eventsByDate[toDateKey(day)] ?? []"
                :key="event.id"
                :event="event"
            />
        </div>
    </div>
</template>
