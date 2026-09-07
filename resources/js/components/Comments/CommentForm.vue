<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import MentionAutocomplete from '@/components/Comments/MentionAutocomplete.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { UserOption } from '@/types';

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

const textareaRef = ref<HTMLTextAreaElement | null>(null);
const mentionQuery = ref('');
const mentionStartIndex = ref<number | null>(null);

function slugify(name: string): string {
    return name.toLowerCase().replace(/[^a-z0-9]/g, '');
}

function onInput(): void {
    const textarea = textareaRef.value;

    if (!textarea) {
        return;
    }

    const cursor = textarea.selectionStart ?? form.content.length;
    const beforeCursor = form.content.slice(0, cursor);
    const match = /@([a-zA-Z0-9]*)$/.exec(beforeCursor);

    if (match) {
        mentionStartIndex.value = match.index;
        mentionQuery.value = match[1];
    } else {
        mentionStartIndex.value = null;
        mentionQuery.value = '';
    }
}

function selectMention(user: UserOption): void {
    const textarea = textareaRef.value;

    if (!textarea || mentionStartIndex.value === null) {
        return;
    }

    const cursor = textarea.selectionStart ?? form.content.length;
    const before = form.content.slice(0, mentionStartIndex.value);
    const after = form.content.slice(cursor);

    form.content = `${before}@${slugify(user.name)} ${after}`;
    mentionStartIndex.value = null;
    mentionQuery.value = '';
}

function submit(): void {
    form.post('/comments', {
        preserveScroll: true,
        onSuccess: () => form.reset('content'),
    });
}
</script>

<template>
    <form class="relative space-y-2" @submit.prevent="submit">
        <textarea
            ref="textareaRef"
            v-model="form.content"
            rows="3"
            class="w-full rounded-md border border-gray-600 px-3 py-2 text-sm text-gray-300"
            placeholder="Add a comment..."
            @input="onInput"
        />
        <MentionAutocomplete
            v-if="mentionStartIndex !== null"
            :query="mentionQuery"
            @select="selectMention"
        />
        <InputError :message="form.errors.content" />
        <Button type="submit" :disabled="form.processing"> Comment </Button>
    </form>
</template>
