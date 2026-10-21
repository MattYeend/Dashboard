<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SelectOption } from '@/types';
import QuestionForm from './components/QuestionForm.vue';

defineProps<{
    typeOptions: SelectOption[];
    questionableTypes: SelectOption[];
}>();
</script>

<template>
    <Head title="Create question" />

    <AppLayout>
        <div class="grid gap-6 p-4">
            <h1 class="text-2xl font-semibold">Create question</h1>
            <QuestionForm
                :type-options="typeOptions"
                :questionable-types="questionableTypes"
            />
        </div>
    </AppLayout>
</template>
