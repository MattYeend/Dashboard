<script setup lang="ts">
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import CalendarGrid from './components/CalendarGrid.vue';
import { events as eventsRoute } from '@/routes/calendar';
import type { CalendarEvent } from '@/types';

type ViewMode = 'month' | 'week';

const viewMode = ref<ViewMode>('month');
const currentDate = ref(new Date());
const calendarEvents = ref<CalendarEvent[]>([]);
const isLoading = ref(false);

const rangeStart = computed(() => {
    const date = new Date(currentDate.value);

    if (viewMode.value === 'month') {
        date.setDate(1);
    } else {
        date.setDate(date.getDate() - date.getDay());
    }

    return date;
});

const rangeEnd = computed(() => {
    const date = new Date(rangeStart.value);

    if (viewMode.value === 'month') {
        date.setMonth(date.getMonth() + 1);
        date.setDate(0);
    } else {
        date.setDate(date.getDate() + 6);
    }

    return date;
});

const formatDate = (date: Date): string => date.toISOString().split('T')[0];

const fetchEvents = async (): Promise<void> => {
    isLoading.value = true;

    try {
        const response = await axios.get(eventsRoute().url, {
            params: {
                start: formatDate(rangeStart.value),
                end: formatDate(rangeEnd.value),
            },
        });

        calendarEvents.value = response.data.data;
    } finally {
        isLoading.value = false;
    }
};

const setViewMode = (mode: ViewMode): void => {
    viewMode.value = mode;
    fetchEvents();
};

const goToToday = (): void => {
    currentDate.value = new Date();
    fetchEvents();
};

const goToPrevious = (): void => {
    const date = new Date(currentDate.value);

    if (viewMode.value === 'month') {
        date.setMonth(date.getMonth() - 1);
    } else {
        date.setDate(date.getDate() - 7);
    }

    currentDate.value = date;
    fetchEvents();
};

const goToNext = (): void => {
    const date = new Date(currentDate.value);

    if (viewMode.value === 'month') {
        date.setMonth(date.getMonth() + 1);
    } else {
        date.setDate(date.getDate() + 7);
    }

    currentDate.value = date;
    fetchEvents();
};

onMounted(fetchEvents);
</script>

<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <div class="flex gap-2">
                <button @click="goToPrevious" class="text-sm text-gray-300">
                    Previous
                </button>
                <button @click="goToToday" class="text-sm text-gray-300">
                    Today
                </button>
                <button @click="goToNext" class="text-sm text-gray-300">
                    Next
                </button>
            </div>
            <div class="flex gap-2">
                <button
                    @click="setViewMode('month')"
                    :class="
                        viewMode === 'month' ? 'text-white' : 'text-gray-400'
                    "
                    class="text-sm"
                >
                    Month
                </button>
                <button
                    @click="setViewMode('week')"
                    :class="
                        viewMode === 'week' ? 'text-white' : 'text-gray-400'
                    "
                    class="text-sm"
                >
                    Week
                </button>
            </div>
        </div>

        <CalendarGrid
            :view-mode="viewMode"
            :current-date="currentDate"
            :events="calendarEvents"
            :is-loading="isLoading"
        />
    </div>
</template>
