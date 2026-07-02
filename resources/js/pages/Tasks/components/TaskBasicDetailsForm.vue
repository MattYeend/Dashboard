<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
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
import { Textarea } from '@/components/ui/textarea';
import type { TaskStatus } from '@/types';

interface TaskFormData {
    title: string;
    description: string | null;
    due_date: string | null;
    assigned_date: string | null;
    assigned_to: number | null;
    status_id: number | null;
}

interface Props {
    statuses: TaskStatus[];
    errors: Partial<InertiaFormProps<TaskFormData>['errors']>;
}

defineProps<Props>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const statusId = defineModel<number | null>('statusId', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title"
                >Title <span class="text-destructive">*</span></Label
            >
            <Input
                id="title"
                v-model="title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter task title"
            />
            <InputError :message="errors.title" />
        </div>

        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="description ?? ''"
                class="mt-1 block w-full"
                rows="4"
                placeholder="Enter task description"
                @update:model-value="description = ($event as string) || null"
            />
            <InputError :message="errors.description" />
        </div>

        <div>
            <Label for="status_id">Status</Label>
            <Select v-model="statusId">
                <SelectTrigger id="status_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select a status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="status in statuses"
                        :key="status.id"
                        :value="status.id"
                    >
                        {{ status.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.status_id" />
        </div>
    </div>
</template>
