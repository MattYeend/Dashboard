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
import { Textarea } from '@/components/ui/textarea';
import type { ReportType } from '@/types';

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    required: true,
});
const type = defineModel<string>('type', { required: true });
const format = defineModel<string>('format', { required: true });

const descriptionValue = computed({
    get: () => description.value ?? '',
    set: (value: string) => {
        description.value = value;
    },
});
defineProps<{
    errors: Partial<Record<string, string>>;
    reportTypes: ReportType[];
}>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title">Title</Label>
            <Input id="title" v-model="title" type="text" />
            <InputError :message="errors.title" />
        </div>

        <div>
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="descriptionValue" rows="3" />
            <InputError :message="errors.description" />
        </div>

        <div>
            <Label for="type">Report covers</Label>
            <Select v-model="type">
                <SelectTrigger id="type">
                    <SelectValue placeholder="Select what the report covers" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in reportTypes"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.type" />
        </div>

        <div>
            <Label for="format">Format</Label>
            <Select v-model="format">
                <SelectTrigger id="format">
                    <SelectValue placeholder="Select a format" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="pdf">PDF</SelectItem>
                    <SelectItem value="csv">CSV</SelectItem>
                    <SelectItem value="xlsx">XLSX</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.format" />
        </div>
    </div>
</template>
