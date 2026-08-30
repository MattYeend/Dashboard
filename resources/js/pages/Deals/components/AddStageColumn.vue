<script setup lang="ts">
import axios from 'axios';
import { ref } from 'vue';
import { store as pipelineStageStore } from '@/routes/pipelines/stages';
import type { Deal } from '@/types';

interface BoardStage {
    id: number;
    name: string;
    deals: Deal[];
}

interface Props {
    pipelineId: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (event: 'stage-added', stage: BoardStage): void;
}>();

const isAdding = ref(false);
const title = ref('');
const processing = ref(false);
const error = ref<string | null>(null);

function startAdding(): void {
    isAdding.value = true;
}

function cancel(): void {
    isAdding.value = false;
    title.value = '';
    error.value = null;
}

async function submit(): Promise<void> {
    if (!title.value.trim()) {
        return;
    }

    processing.value = true;
    error.value = null;

    try {
        const response = await axios.post(
            pipelineStageStore.url(props.pipelineId),
            { title: title.value },
            { headers: { Accept: 'application/json' } },
        );

        emit('stage-added', {
            id: response.data.id,
            name: response.data.title,
            deals: [],
        });

        cancel();
    } catch {
        error.value = 'Could not add the column. Please try again.';
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <div
        class="flex w-72 shrink-0 flex-col rounded border border-dashed border-gray-500"
    >
        <form
            v-if="isAdding"
            class="flex flex-col gap-2 p-2"
            @submit.prevent="submit"
        >
            <input
                v-model="title"
                type="text"
                placeholder="Column title"
                class="rounded border border-gray-500 px-2 py-1 text-sm text-gray-200"
                autofocus
            />
            <p v-if="error" class="text-xs text-red-400">
                {{ error }}
            </p>
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="rounded border border-gray-500 px-2 py-1 text-xs text-gray-200"
                    :disabled="processing"
                >
                    Add column
                </button>
                <button
                    type="button"
                    class="text-xs text-gray-400 hover:text-gray-200"
                    @click="cancel"
                >
                    Cancel
                </button>
            </div>
        </form>

        <button
            v-else
            type="button"
            class="p-3 text-left text-sm text-gray-400 hover:text-gray-200"
            @click="startAdding"
        >
            + Add column
        </button>
    </div>
</template>
