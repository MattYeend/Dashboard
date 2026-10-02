<script setup lang="ts">
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const type = defineModel<string>('type', { required: true });
const search = defineModel<string>('search', { required: true });
</script>

<template>
    <div class="mb-4 flex flex-wrap gap-2">
        <Select v-model="type">
            <SelectTrigger class="w-40 border-gray-500 text-gray-300">
                <SelectValue placeholder="All types" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">All types</SelectItem>
                <SelectItem value="note">Note</SelectItem>
                <SelectItem value="status_change">Status change</SelectItem>
                <SelectItem value="task_created">Task created</SelectItem>
                <SelectItem value="call_logged">Call logged</SelectItem>
                <SelectItem value="email_logged">Email logged</SelectItem>
            </SelectContent>
        </Select>
        <Input
            v-model="search"
            placeholder="Search description..."
            class="max-w-xs border-gray-500 text-gray-300"
        />
    </div>
</template>
