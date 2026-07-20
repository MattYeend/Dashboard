<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import PostForm from '@/pages/Posts/components/PostForm.vue';
import { update as postsUpdate } from '@/routes/posts';
import type { Post } from '@/types';

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    post: Post;
    categories: CategoryOption[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.post.title,
    description: props.post.description,
    image: null as File | null,
    category_ids: props.post.categories?.map((category) => category.id) ?? [],
});

function onFileChange(file: File | null): void {
    form.image = file;
}

function submit(): void {
    form.put(postsUpdate.url(props.post.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Edit Post</h1>

            <PostForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:category-ids="form.category_ids"
                :categories="categories"
                :existing-image="post.image"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
                @update:file="onFileChange"
            />
        </div>
    </div>
</template>
