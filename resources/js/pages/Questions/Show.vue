<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { destroy, edit, index, restore } from '@/routes/questions';
import type { Question, QuestionAbilities } from '@/types';
import QuestionAuditDetails from './components/QuestionAuditDetails.vue';
import QuestionBasicDetails from './components/QuestionBasicDetails.vue';
import QuestionLinkDetails from './components/QuestionLinkDetails.vue';
import QuestionTypeDetails from './components/QuestionTypeDetails.vue';

const props = defineProps<{
    question: Question;
    abilities: QuestionAbilities;
}>();

function remove(): void {
    if (window.confirm('Delete this question?')) {
        router.delete(destroy.url(props.question.id));
    }
}

function restoreQuestion(): void {
    router.post(restore.url(props.question.id));
}
</script>

<template>
    <Head title="Question" />

    <AppLayout>
        <div class="grid max-w-3xl gap-6 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    Question #{{ question.id }}
                </h1>
                <div class="flex gap-2">
                    <Button as-child variant="outline">
                        <Link :href="index.url()">Back</Link>
                    </Button>
                    <Button v-if="abilities.can_update" as-child>
                        <Link :href="edit.url(question.id)">Edit</Link>
                    </Button>
                    <Button
                        v-if="abilities.can_delete"
                        variant="destructive"
                        @click="remove"
                    >
                        Delete
                    </Button>
                    <Button
                        v-if="abilities.can_restore"
                        @click="restoreQuestion"
                    >
                        Restore
                    </Button>
                </div>
            </div>

            <QuestionLinkDetails :question="question" />
            <QuestionBasicDetails :question="question" />
            <QuestionTypeDetails :question="question" />
            <QuestionAuditDetails :question="question" />
        </div>
    </AppLayout>
</template>
