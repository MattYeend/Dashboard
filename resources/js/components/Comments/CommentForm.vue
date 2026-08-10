<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

interface Props {
    commentableType: string;
    commentableId: number;
}

const props = defineProps<Props>();

const form = useForm({
    commentable_type: props.commentableType,
    commentable_id: props.commentableId,
    content: '',
});

function submit(): void {
    form.post('/comments', {
        preserveScroll: true,
        onSuccess: () => form.reset('content'),
    });
}
</script>

<template>
    <form class="space-y-2" @submit.prevent="submit">
        <textarea
            v-model="form.content"
            rows="3"
            class="w-full rounded-md border border-gray-600 px-3 py-2 text-sm text-gray-300"
            placeholder="Add a comment..."
        />
        <InputError :message="form.errors.content" />
        <Button type="submit" :disabled="form.processing"> Comment </Button>
    </form>
</template>
