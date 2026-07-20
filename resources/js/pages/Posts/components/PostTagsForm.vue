<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

interface TagOption {
    id: number;
    name: string;
}

interface Props {
    tags: TagOption[];
    errors: Partial<Record<'tag_ids', string>>;
}

defineProps<Props>();

const tagIds = defineModel<number[]>('tagIds', { required: true });

function toggle(id: number, checked: boolean): void {
    if (checked) {
        tagIds.value = [...tagIds.value, id];

        return;
    }

    tagIds.value = tagIds.value.filter((tagId) => tagId !== id);
}
</script>

<template>
    <div>
        <Label>Tags</Label>
        <div class="mt-2 space-y-2">
            <div
                v-for="tag in tags"
                :key="tag.id"
                class="flex items-center space-x-2"
            >
                <Checkbox
                    :id="`tag_${tag.id}`"
                    :model-value="tagIds.includes(tag.id)"
                    @update:model-value="(checked) => toggle(tag.id, !!checked)"
                />
                <Label :for="`tag_${tag.id}`">
                    {{ tag.name }}
                </Label>
            </div>
        </div>
        <InputError :message="errors.tag_ids" />
    </div>
</template>
