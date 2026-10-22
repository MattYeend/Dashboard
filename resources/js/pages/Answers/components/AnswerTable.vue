<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/answers';
import type { Answer, PaginationLink } from '@/types';

defineProps<{
    answers: Answer[];
    links: PaginationLink[];
    showCorrect: boolean;
}>();

const selected = defineModel<number[]>('selected', { required: true });

function pageLabel(value: string): string {
    return value.replace(/&laquo;|&raquo;/g, '').trim();
}
</script>

<template>
    <div class="grid gap-4">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b">
                    <th class="w-8 p-2"></th>
                    <th class="p-2">Answer</th>
                    <th class="p-2">Question</th>
                    <th v-if="showCorrect" class="p-2">Correct</th>
                    <th class="p-2">Position</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="answer in answers"
                    :key="answer.id"
                    class="border-b"
                    :class="{ 'opacity-60': answer.deleted_at }"
                >
                    <td class="p-2">
                        <input
                            v-model="selected"
                            type="checkbox"
                            :value="answer.id"
                            :aria-label="`Select answer ${answer.id}`"
                        />
                    </td>
                    <td class="p-2">
                        <Link :href="show.url(answer.id)" class="underline">
                            {{
                                answer.answer.length > 80
                                    ? `${answer.answer.slice(0, 80)}...`
                                    : answer.answer
                            }}
                        </Link>
                    </td>
                    <td class="p-2">#{{ answer.question_id }}</td>
                    <td v-if="showCorrect" class="p-2">
                        {{ answer.is_correct ? 'Yes' : 'No' }}
                    </td>
                    <td class="p-2">{{ answer.position }}</td>
                </tr>
                <tr v-if="answers.length === 0">
                    <td :colspan="showCorrect ? 5 : 4" class="p-4 text-center">
                        No answers found.
                    </td>
                </tr>
            </tbody>
        </table>

        <nav
            v-if="links.length > 3"
            class="flex flex-wrap gap-1"
            aria-label="Pagination"
        >
            <template v-for="link in links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="rounded-md border px-3 py-1 text-sm"
                    :class="{ 'font-semibold underline': link.active }"
                >
                    {{ pageLabel(link.label) }}
                </Link>
                <span
                    v-else
                    class="rounded-md border px-3 py-1 text-sm opacity-50"
                >
                    {{ pageLabel(link.label) }}
                </span>
            </template>
        </nav>
    </div>
</template>
