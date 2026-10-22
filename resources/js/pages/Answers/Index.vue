<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, index } from '@/routes/answers';
import bulk from '@/routes/answers/bulk';
import type {
    Answer,
    AnswerPermissionsMeta,
    Pagination,
    PaginationLink,
} from '@/types';
import AnswerFilters from './components/AnswerFilters.vue';
import AnswerTable from './components/AnswerTable.vue';

interface PaginatedAnswers {
    data: Answer[];
    links: PaginationLink[];
    meta: Pagination;
}

const props = defineProps<{
    answers: PaginatedAnswers;
    filters: {
        search?: string;
        is_correct?: string | boolean;
        trashed?: string;
    };
    permissions_meta: AnswerPermissionsMeta;
}>();

const state = reactive({
    search: props.filters.search ?? '',
    isCorrect:
        props.filters.is_correct === undefined
            ? ''
            : props.filters.is_correct
              ? '1'
              : '0',
    trashed: props.filters.trashed ?? '',
});

const selected = ref<number[]>([]);

function apply(): void {
    router.get(
        index.url(),
        {
            search: state.search || undefined,
            is_correct: state.isCorrect || undefined,
            trashed: state.trashed || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function reset(): void {
    state.search = '';
    state.isCorrect = '';
    state.trashed = '';
    apply();
}

function bulkAction(action: 'delete' | 'restore'): void {
    if (selected.value.length === 0) {
        return;
    }

    const url = action === 'delete' ? bulk.delete.url() : bulk.restore.url();

    router.post(
        url,
        { ids: selected.value },
        { onSuccess: () => (selected.value = []) },
    );
}
</script>

<template>
    <Head title="Answers" />

    <AppLayout>
        <div class="grid gap-6 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Answers</h1>
                <Button v-if="permissions_meta.can_create" as-child>
                    <Link :href="create.url()">New answer</Link>
                </Button>
            </div>

            <AnswerFilters
                v-model:search="state.search"
                v-model:is-correct="state.isCorrect"
                v-model:trashed="state.trashed"
                :can-view-trashed="permissions_meta.can_restore"
                :can-view-correct="permissions_meta.can_view_correct"
                @apply="apply"
                @reset="reset"
            />

            <div
                v-if="selected.length > 0"
                class="flex items-center gap-2 text-sm"
            >
                <span>{{ selected.length }} selected</span>
                <Button
                    v-if="permissions_meta.can_delete"
                    variant="destructive"
                    size="sm"
                    @click="bulkAction('delete')"
                >
                    Delete
                </Button>
                <Button
                    v-if="permissions_meta.can_restore"
                    variant="outline"
                    size="sm"
                    @click="bulkAction('restore')"
                >
                    Restore
                </Button>
            </div>

            <AnswerTable
                v-model:selected="selected"
                :answers="answers.data"
                :links="answers.links"
                :show-correct="permissions_meta.can_view_correct"
            />
        </div>
    </AppLayout>
</template>
