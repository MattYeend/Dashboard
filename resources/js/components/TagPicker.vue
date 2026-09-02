<script setup lang="ts">
import axios from 'axios';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as tagsStore } from '@/routes/tags';
import type { Tag } from '@/types';

interface TagOption {
    id: number;
    name: string;
}

interface Props {
    tags: TagOption[];
    errors?: Partial<Record<'tag_ids', string>>;
    canCreate?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    errors: () => ({}),
    canCreate: false,
});

const tagIds = defineModel<number[]>('tagIds', { required: true });

const search = ref('');
const creating = ref(false);
const localTags = ref<TagOption[]>([...props.tags]);

const filteredTags = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return localTags.value;
    }

    return localTags.value.filter((tag) =>
        tag.name.toLowerCase().includes(term),
    );
});

const exactMatchExists = computed(() =>
    localTags.value.some(
        (tag) => tag.name.toLowerCase() === search.value.trim().toLowerCase(),
    ),
);

const selectedTags = computed(() =>
    localTags.value.filter((tag) => tagIds.value.includes(tag.id)),
);

function toggle(id: number): void {
    if (tagIds.value.includes(id)) {
        tagIds.value = tagIds.value.filter((tagId) => tagId !== id);

        return;
    }

    tagIds.value = [...tagIds.value, id];
}

function remove(id: number): void {
    tagIds.value = tagIds.value.filter((tagId) => tagId !== id);
}

async function createTag(): Promise<void> {
    const name = search.value.trim();

    if (!name || creating.value) {
        return;
    }

    creating.value = true;

    try {
        const response = await axios.post<Tag>(
            tagsStore.url(),
            { name },
            { headers: { Accept: 'application/json' } },
        );

        localTags.value = [
            ...localTags.value,
            { id: response.data.id, name: response.data.name },
        ];
        tagIds.value = [...tagIds.value, response.data.id];
        search.value = '';
    } finally {
        creating.value = false;
    }
}
</script>

<template>
    <div>
        <Label for="tag-search">Tags</Label>

        <div v-if="selectedTags.length" class="mt-2 flex flex-wrap gap-2">
            <span
                v-for="tag in selectedTags"
                :key="tag.id"
                class="inline-flex items-center gap-1 rounded-full border border-gray-500 px-2 py-0.5 text-xs text-gray-300"
            >
                {{ tag.name }}
                <button
                    type="button"
                    class="text-gray-400 hover:text-gray-200"
                    @click="remove(tag.id)"
                >
                    &times;
                </button>
            </span>
        </div>

        <Input
            id="tag-search"
            v-model="search"
            type="text"
            class="mt-2 block w-full"
            placeholder="Search or add tags…"
            @keydown.enter.prevent="
                canCreate && !exactMatchExists ? createTag() : null
            "
        />

        <div
            v-if="search || filteredTags.length"
            class="mt-2 max-h-40 space-y-1 overflow-y-auto rounded-md border border-gray-500 p-2"
        >
            <button
                v-for="tag in filteredTags"
                :key="tag.id"
                type="button"
                class="block w-full rounded px-2 py-1 text-left text-sm"
                :class="
                    tagIds.includes(tag.id) ? 'text-gray-100' : 'text-gray-400'
                "
                @click="toggle(tag.id)"
            >
                {{ tagIds.includes(tag.id) ? '✓ ' : '' }}{{ tag.name }}
            </button>

            <button
                v-if="canCreate && search && !exactMatchExists"
                type="button"
                class="block w-full rounded px-2 py-1 text-left text-sm text-blue-400"
                :disabled="creating"
                @click="createTag"
            >
                + Create "{{ search }}"
            </button>
        </div>

        <InputError :message="errors.tag_ids" />
    </div>
</template>
