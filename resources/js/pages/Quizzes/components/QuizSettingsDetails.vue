<script setup lang="ts">
import type { Quiz } from '@/types';

defineProps<{
    quiz: Quiz;
}>();
</script>

<template>
    <dl class="grid gap-4 border-b border-gray-500 pb-4 sm:grid-cols-3">
        <div>
            <dt class="text-xs text-gray-400">Pass mark</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.pass_mark_percentage }}%
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Time limit</dt>
            <dd class="text-sm text-gray-300">
                {{
                    quiz.time_limit_minutes
                        ? `${quiz.time_limit_minutes} minutes`
                        : 'None'
                }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Maximum attempts</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.max_attempts ?? 'Unlimited' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Shuffle questions</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.shuffle_questions ? 'Yes' : 'No' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Show correct answers</dt>
            <dd class="text-sm text-gray-300">
                {{ quiz.show_correct_answers ? 'Yes' : 'No' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Published</dt>
            <dd class="text-sm text-gray-300">
                {{
                    quiz.published_at
                        ? new Date(quiz.published_at).toLocaleDateString(
                              'en-GB',
                          )
                        : 'Draft'
                }}
            </dd>
        </div>
    </dl>
</template>
