<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    categories: CategoryOption[];
    errors: Partial<Record<'category_ids', string>>;
}

defineProps<Props>();

const categoryIds = defineModel<number[]>('categoryIds', { required: true });

function toggle(id: number, checked: boolean): void {
    if (checked) {
        categoryIds.value = [...categoryIds.value, id];

        return;
    }

    categoryIds.value = categoryIds.value.filter(
        (categoryId) => categoryId !== id,
    );
}
</script>

<template>
    <div>
        <Label>Categories</Label>
        <div class="mt-2 space-y-2">
            <div
                v-for="category in categories"
                :key="category.id"
                class="flex items-center space-x-2"
            >
                <Checkbox
                    :id="`category_${category.id}`"
                    :model-value="categoryIds.includes(category.id)"
                    @update:model-value="
                        (checked) => toggle(category.id, !!checked)
                    "
                />
                <Label :for="`category_${category.id}`">
                    {{ category.name }}
                </Label>
            </div>
        </div>
        <InputError :message="errors.category_ids" />
    </div>
</template>
