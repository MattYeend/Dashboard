<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { show } from '@/routes/questions';
import type { Question } from '@/types';

defineProps<{
    questions: Question[];
    canRestore: boolean;
}>();

const emit = defineEmits<{
    delete: [id: number];
    'bulk-delete': [ids: Array<number | string>];
    'bulk-restore': [ids: Array<number | string>];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});
</script>

<template>
    <div class="grid gap-4">
        <div v-if="selected.length > 0" class="flex items-center gap-2 text-sm">
            <span>{{ selected.length }} selected</span>
            <Button
                variant="destructive"
                size="sm"
                @click="emit('bulk-delete', selected)"
                >Delete</Button
            >
            <Button
                v-if="canRestore"
                variant="outline"
                size="sm"
                @click="emit('bulk-restore', selected)"
                >Restore</Button
            >
        </div>

        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b">
                    <th class="w-8 p-2"></th>
                    <th class="p-2">Question</th>
                    <th class="p-2">Belongs to</th>
                    <th class="p-2">Type</th>
                    <th class="p-2">Points</th>
                    <th class="p-2">Position</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="question in questions"
                    :key="question.id"
                    class="border-b"
                    :class="{ 'opacity-60': question.deleted_at }"
                >
                    <td class="p-2">
                        <input
                            v-model="selected"
                            type="checkbox"
                            :value="question.id"
                            :aria-label="`Select question ${question.id}`"
                        />
                    </td>
                    <td class="p-2">
                        <Link :href="show.url(question.id)" class="underline">
                            {{
                                question.question.length > 80
                                    ? `${question.question.slice(0, 80)}...`
                                    : question.question
                            }}
                        </Link>
                    </td>
                    <td class="p-2">
                        <span class="capitalize">{{
                            question.questionable_type
                        }}</span
                        >:
                        {{
                            question.questionable?.label ??
                            `#${question.questionable_id}`
                        }}
                    </td>
                    <td class="p-2">{{ question.type_label }}</td>
                    <td class="p-2">{{ question.points }}</td>
                    <td class="p-2">{{ question.position }}</td>
                    <td class="p-2 text-right">
                        <Button
                            v-if="!question.deleted_at"
                            variant="destructive"
                            size="sm"
                            @click="emit('delete', question.id)"
                        >
                            Delete
                        </Button>
                    </td>
                </tr>
                <tr v-if="questions.length === 0">
                    <td colspan="7" class="p-4 text-center">
                        No questions found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
