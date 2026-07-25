<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as tagsStore } from '@/routes/tags';
import TagForm from './components/TagForm.vue';

const form = useForm({
    name: '',
    slug: null as string | null,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        slug: nullIfBlank(data.slug),
    })).post(tagsStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Tag
            </h1>
            <form class="space-y-6" @submit.prevent="submit">
                <TagForm
                    v-model:name="form.name"
                    v-model:slug="form.slug"
                    :errors="form.errors"
                />

                <div class="flex items-center justify-end space-x-3">
                    <button type="submit" :disabled="form.processing">
                        Create Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
