<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const isScheduled = defineModel<boolean>('isScheduled', { required: true });
const scheduleFrequency = defineModel<string | null>('scheduleFrequency', {
    required: true,
});
const scheduleTime = defineModel<string | null>('scheduleTime', {
    required: true,
});
const recipients = defineModel<string>('recipients', { required: true });

const scheduleTimeValue = computed({
    get: () => scheduleTime.value ?? '',
    set: (value: string) => {
        scheduleTime.value = value === '' ? null : value;
    },
});

defineProps<{
    errors: Partial<Record<string, string>>;
    canSchedule: boolean;
}>();
</script>

<template>
    <div class="space-y-4">
        <label class="flex items-center gap-2 text-sm text-gray-300">
            <input
                type="checkbox"
                :checked="isScheduled"
                :disabled="!canSchedule"
                @change="
                    isScheduled = ($event.target as HTMLInputElement).checked
                "
            />
            Run this report automatically
        </label>
        <p v-if="!canSchedule" class="text-xs text-gray-400">
            You do not have permission to schedule reports.
        </p>

        <template v-if="isScheduled">
            <div>
                <Label for="schedule_frequency">Frequency</Label>
                <Select v-model="scheduleFrequency" :disabled="!canSchedule">
                    <SelectTrigger id="schedule_frequency">
                        <SelectValue placeholder="Select a frequency" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="daily">Daily</SelectItem>
                        <SelectItem value="weekly">Weekly</SelectItem>
                        <SelectItem value="monthly">Monthly</SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.schedule_frequency" />
            </div>

            <div>
                <Label for="schedule_time">Time</Label>
                <Input
                    id="schedule_time"
                    v-model="scheduleTimeValue"
                    type="time"
                    :disabled="!canSchedule"
                />
                <InputError :message="errors.schedule_time" />
            </div>

            <div>
                <Label for="recipients">Recipients</Label>
                <Input
                    id="recipients"
                    v-model="recipients"
                    type="text"
                    placeholder="Comma-separated email addresses"
                    :disabled="!canSchedule"
                />
                <InputError :message="errors.recipients" />
            </div>
        </template>
    </div>
</template>
