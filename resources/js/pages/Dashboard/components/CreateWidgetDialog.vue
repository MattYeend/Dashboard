<script setup lang="ts">
import axios from 'axios';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { store as storeCustomWidget } from '@/routes/dashboard/custom-widgets';
import type { DashboardMetric } from '@/types';

const props = defineProps<{
    metrics: DashboardMetric[];
}>();

const emit = defineEmits<{
    created: [];
}>();

const dateRangeOptions = [
    { value: 'today', label: 'Today' },
    { value: 'this_week', label: 'This week' },
    { value: 'this_month', label: 'This month' },
    { value: 'this_year', label: 'This year' },
    { value: 'all_time', label: 'All time' },
];

const isOpen = ref(false);
const isSubmitting = ref(false);
const label = ref('');
const description = ref('');
const metricKey = ref('');
const dateRange = ref('all_time');

async function submit(): Promise<void> {
    if (!label.value || !metricKey.value) {
        return;
    }

    isSubmitting.value = true;

    try {
        await axios.post(storeCustomWidget.url(), {
            label: label.value,
            description: description.value || null,
            metric_key: metricKey.value,
            date_range: dateRange.value,
        });

        label.value = '';
        description.value = '';
        metricKey.value = '';
        dateRange.value = 'all_time';
        isOpen.value = false;
        emit('created');
    } catch (error) {
        console.error('Failed to create widget:', error);
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                <Plus class="size-4" />
                New widget
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Create a widget</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="widget-label">Label</Label>
                    <Input
                        id="widget-label"
                        v-model="label"
                        placeholder="e.g. New contacts"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="widget-description">Description</Label>
                    <Textarea
                        id="widget-description"
                        v-model="description"
                        placeholder="Optional note about what this widget shows"
                        rows="3"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="widget-metric">Metric</Label>
                    <Select v-model="metricKey">
                        <SelectTrigger id="widget-metric">
                            <SelectValue placeholder="Choose a metric" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="metric in props.metrics"
                                :key="metric.key"
                                :value="metric.key"
                            >
                                {{ metric.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-2">
                    <Label for="widget-range">Date range</Label>
                    <Select v-model="dateRange">
                        <SelectTrigger id="widget-range">
                            <SelectValue placeholder="Choose a range" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in dateRangeOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DialogFooter>
                <Button :disabled="isSubmitting" @click="submit">
                    Create widget
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
