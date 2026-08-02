<script setup lang="ts">
import Blockquote from '@tiptap/extension-blockquote';
import Bold from '@tiptap/extension-bold';
import BulletList from '@tiptap/extension-bullet-list';
import Document from '@tiptap/extension-document';
import Heading from '@tiptap/extension-heading';
import History from '@tiptap/extension-history';
import Italic from '@tiptap/extension-italic';
import Link from '@tiptap/extension-link';
import ListItem from '@tiptap/extension-list-item';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const title = defineModel<string>('title', { required: true });
const description = defineModel<string>('description', { required: true });

interface Props {
    errors: Partial<Record<'title' | 'description', string>>;
}

defineProps<Props>();

const editor = useEditor({
    content: description.value,
    extensions: [
        Document,
        Paragraph,
        Text,
        Bold,
        Italic,
        Heading.configure({ levels: [2] }),
        Blockquote,
        BulletList,
        ListItem,
        History,
        Link.configure({ openOnClick: false }),
    ],
    onUpdate: ({ editor: currentEditor }) => {
        description.value = currentEditor.getHTML();
    },
});

watch(description, (value) => {
    if (editor.value && value !== editor.value.getHTML()) {
        editor.value.commands.setContent(value, { emitUpdate: false });
    }
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title">Title</Label>
            <Input
                id="title"
                v-model="title"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.title" />
        </div>
        <div>
            <Label for="description">Description</Label>
            <div class="mt-1 rounded-md border border-gray-600">
                <div class="flex flex-wrap gap-1 border-b border-gray-600 p-2">
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-sm"
                        :class="{ 'font-bold': editor?.isActive('bold') }"
                        @click="editor?.chain().focus().toggleBold().run()"
                    >
                        Bold
                    </button>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-sm italic"
                        :class="{ underline: editor?.isActive('italic') }"
                        @click="editor?.chain().focus().toggleItalic().run()"
                    >
                        Italic
                    </button>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-sm"
                        @click="
                            editor?.chain().focus().toggleBulletList().run()
                        "
                    >
                        Bullet list
                    </button>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-sm"
                        @click="
                            editor
                                ?.chain()
                                .focus()
                                .toggleHeading({ level: 2 })
                                .run()
                        "
                    >
                        Heading
                    </button>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-sm"
                        @click="
                            editor?.chain().focus().toggleBlockquote().run()
                        "
                    >
                        Quote
                    </button>
                </div>
                <EditorContent
                    :editor="editor"
                    class="prose prose-sm max-w-none p-3 text-gray-300"
                />
            </div>
            <InputError :message="errors.description" />
        </div>
    </div>
</template>
