<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { destroy, edit, index, restore } from '@/routes/answers';
import type { Answer, AnswerAbilities, AnswerPermissionsMeta } from '@/types';
import AnswerAuditDetails from './components/AnswerAuditDetails.vue';
import AnswerBasicDetails from './components/AnswerBasicDetails.vue';
import AnswerCorrectnessDetails from './components/AnswerCorrectnessDetails.vue';
import AnswerQuestionDetails from './components/AnswerQuestionDetails.vue';

const props = defineProps<{
    answer: Answer;
    abilities: AnswerAbilities;
    permissions_meta: AnswerPermissionsMeta;
}>();

function remove(): void {
    if (window.confirm('Delete this answer?')) {
        router.delete(destroy.url(props.answer.id));
    }
}

function restoreAnswer(): void {
    router.post(restore.url(props.answer.id));
}
</script>

<template>
    <Head title="Answer" />

    <AppLayout>
        <div class="grid max-w-3xl gap-6 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Answer #{{ answer.id }}</h1>
                <div class="flex gap-2">
                    <Button as-child variant="outline">
                        <Link :href="index.url()">Back</Link>
                    </Button>
                    <Button
                        v-if="abilities.can_update && !answer.deleted_at"
                        as-child
                    >
                        <Link :href="edit.url(answer.id)">Edit</Link>
                    </Button>
                    <Button
                        v-if="abilities.can_delete && !answer.deleted_at"
                        variant="destructive"
                        @click="remove"
                    >
                        Delete
                    </Button>
                    <Button
                        v-if="abilities.can_restore && answer.deleted_at"
                        @click="restoreAnswer"
                    >
                        Restore
                    </Button>
                </div>
            </div>

            <AnswerQuestionDetails :answer="answer" />
            <AnswerBasicDetails :answer="answer" />
            <AnswerCorrectnessDetails
                v-if="permissions_meta.can_view_correct"
                :answer="answer"
            />
            <AnswerAuditDetails :answer="answer" />
        </div>
    </AppLayout>
</template>
