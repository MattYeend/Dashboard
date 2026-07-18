<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import PostCategoriesForm from '@/pages/Posts/components/PostCategoriesForm.vue';
import PostDetailsForm from '@/pages/Posts/components/PostDetailsForm.vue';
import PostImageForm from '@/pages/Posts/components/PostImageForm.vue';
import { index as postsIndex } from '@/routes/posts';

interface PostFormData {
    title: string;
    description: string;
    image: File | null;
    category_ids: number[];
}

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<PostFormData>['errors']>;
    categories: CategoryOption[];
    existingImage?: string | null;
}

defineProps<Props>();
defineEmits<{ submit: []; 'update:file': [File | null] }>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string>('description', { required: true });
const categoryIds = defineModel<number[]>('categoryIds', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <PostDetailsForm
            v-model:title="title"
            v-model:description="description"
            :errors="errors"
        />

        <PostImageForm
            :existing-image="existingImage"
            :errors="errors"
            @update:file="$emit('update:file', $event)"
        />

        <PostCategoriesForm
            v-model:category-ids="categoryIds"
            :categories="categories"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="postsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Post' : 'Create Post' }}
            </Button>
        </div>
    </form>
</template>
