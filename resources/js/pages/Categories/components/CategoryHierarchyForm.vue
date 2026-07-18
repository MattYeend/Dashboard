<script setup lang="ts">
import { computed } from 'vue';
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

const NO_PARENT = 'none';

const selectValue = computed<string | undefined>({
    get: () => (parentId.value === null ? NO_PARENT : String(parentId.value)),
    set: (value) => {
        parentId.value =
            value === undefined || value === NO_PARENT ? null : Number(value);
    },
});
</script>

<template>
    <div>
        <Label for="parent_id">Parent category</Label>
        <Select v-model="selectValue">
            <SelectTrigger id="parent_id" class="mt-1 w-full">
                <SelectValue placeholder="No parent (top level)" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem :value="NO_PARENT">
                    No parent (top level)
                </SelectItem>
                <SelectItem
                    v-for="option in parentOptions"
                    :key="option.value"
                    :value="String(option.value)"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.parent_id" />
    </div>
</template>
