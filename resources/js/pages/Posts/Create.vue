<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import PostForm from '@/pages/Posts/components/PostForm.vue';
import { store as postsStore } from '@/routes/posts';

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    categories: CategoryOption[];
}

defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    image: null as File | null,
    category_ids: [] as number[],
});

function onFileChange(file: File | null): void {
    form.image = file;
}

function submit(): void {
    form.post(postsStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Post</h1>

            <PostForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:category-ids="form.category_ids"
                :categories="categories"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
                @update:file="onFileChange"
            />
        </div>
    </div>
</template>
