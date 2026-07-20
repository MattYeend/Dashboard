<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import type { Tag } from '@/types';
import TagForm from './components/TagForm.vue';
import { update as tagsUpdate } from '@/routes/tags';

interface Props {
    tag: Tag;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.tag.name,
    slug: props.tag.slug as string | null,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        slug: nullIfBlank(data.slug),
    })).put(tagsUpdate.url(props.tag.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">Edit Tag</h1>
            <form class="space-y-6" @submit.prevent="submit">
                <TagForm
                    v-model:name="form.name"
                    v-model:slug="form.slug"
                    :errors="form.errors"
                />

                <div class="flex items-center justify-end space-x-3">
                    <button type="submit" :disabled="form.processing">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
