<script setup lang="ts">
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import { events as calendarEventsRoute } from '@/routes/calendar';
import type { CalendarEvent } from '@/types';
import CalendarGrid from './components/CalendarGrid.vue';

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
        const response = await axios.get(calendarEventsRoute.url(), {
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
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader title="Calendar" :can-create="false" />

            <div class="mb-4 flex items-center justify-between">
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="text-sm text-gray-300 hover:text-gray-100"
                        @click="goToPrevious"
                    >
                        Previous
                    </button>

                    <button
                        type="button"
                        class="text-sm text-gray-300 hover:text-gray-100"
                        @click="goToToday"
                    >
                        Today
                    </button>

                    <button
                        type="button"
                        class="text-sm text-gray-300 hover:text-gray-100"
                        @click="goToNext"
                    >
                        Next
                    </button>
                </div>

                <div class="text-sm text-gray-300">
                    {{
                        currentDate.toLocaleDateString('en-GB', {
                            month: 'long',
                            year: 'numeric',
                        })
                    }}
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        class="text-sm"
                        :class="
                            viewMode === 'month'
                                ? 'text-white'
                                : 'text-gray-400 hover:text-gray-200'
                        "
                        @click="setViewMode('month')"
                    >
                        Month
                    </button>

                    <button
                        type="button"
                        class="text-sm"
                        :class="
                            viewMode === 'week'
                                ? 'text-white'
                                : 'text-gray-400 hover:text-gray-200'
                        "
                        @click="setViewMode('week')"
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
    </div>
</template>
