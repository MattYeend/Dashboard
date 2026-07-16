<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Props {
    errors: Partial<Record<'parent_id', string>>;
    parentOptions: { value: number; label: string }[];
}

defineProps<Props>();

const parentId = defineModel<number | null>('parentId', { required: true });
</script>

<template>
    <div>
        <Label for="parent_id">Parent category</Label>
        <Select v-model="parentId">
            <SelectTrigger id="parent_id" class="mt-1 w-full">
                <SelectValue placeholder="No parent (top level)" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in parentOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.parent_id" />
    </div>
</template>
