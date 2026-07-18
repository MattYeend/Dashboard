<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import CategoryDetailsForm from '@/pages/Categories/components/CategoryDetailsForm.vue';
import CategoryHierarchyForm from '@/pages/Categories/components/CategoryHierarchyForm.vue';
import { index as categoriesIndex } from '@/routes/categories';

interface CategoryFormData {
    parent_id: number | null;
    name: string;
    slug: string;
    description: string;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<CategoryFormData>['errors']>;
    parentOptions: { value: number; label: string }[];
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const parentId = defineModel<number | null>('parentId', { required: true });
const name = defineModel<string>('name', { required: true });
const description = defineModel<string>('description', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <CategoryHierarchyForm
            v-model:parent-id="parentId"
            :parent-options="parentOptions"
            :errors="errors"
        />

        <CategoryDetailsForm
            v-model:name="name"
            v-model:description="description"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="categoriesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Category' : 'Create Category' }}
            </Button>
        </div>
    </form>
</template>
