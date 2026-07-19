<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { Comment } from '@/types';

interface Props {
    postId: number;
    comment: Comment;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

const isEditing = ref(false);
const editContent = ref(props.comment.content);
const editProcessing = ref(false);
const editErrors = ref<{ content?: string }>({});

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(`/posts/${props.postId}/comments/${props.comment.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

function startEdit(): void {
    editContent.value = props.comment.content;
    editErrors.value = {};
    isEditing.value = true;
}

function cancelEdit(): void {
    isEditing.value = false;
}

function saveEdit(): void {
    editProcessing.value = true;

    router.put(
        `/posts/${props.postId}/comments/${props.comment.id}`,
        { content: editContent.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
            },
            onError: (errors) => {
                editErrors.value = errors;
            },
            onFinish: () => {
                editProcessing.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="border-b border-gray-700 pb-3">
        <template v-if="isEditing">
            <textarea
                v-model="editContent"
                rows="3"
                class="w-full rounded-md border border-gray-600 px-3 py-2 text-sm text-gray-300"
            />
            <InputError :message="editErrors.content" />
            <div class="mt-2 flex gap-2">
                <Button size="sm" :disabled="editProcessing" @click="saveEdit">
                    Save
                </Button>
                <Button size="sm" variant="outline" @click="cancelEdit">
                    Cancel
                </Button>
            </div>
        </template>
        <template v-else>
            <p class="text-sm text-gray-300">{{ comment.content }}</p>
            <div class="mt-1 flex items-center gap-2 text-xs text-gray-400">
                <span>{{ comment.creator?.name ?? '—' }}</span>
                <button
                    v-if="comment.can_update"
                    type="button"
                    class="text-gray-300"
                    @click="startEdit"
                >
                    Edit
                </button>
                <button
                    v-if="comment.can_delete"
                    type="button"
                    class="text-red-600"
                    @click="requestDestroy"
                >
                    Delete
                </button>
            </div>
        </template>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete comment"
            description="This comment will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
