<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import CategoryForm from '@/pages/Categories/components/CategoryForm.vue';
import { store as categoriesStore } from '@/routes/categories';

interface Props {
    parentOptions: { value: number; label: string }[];
}

defineProps<Props>();

const form = useForm({
    parent_id: null as number | null,
    name: '',
    description: '',
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(categoriesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Category</h1>

            <CategoryForm
                v-model:parent-id="form.parent_id"
                v-model:name="form.name"
                v-model:description="form.description"
                :parent-options="parentOptions"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
