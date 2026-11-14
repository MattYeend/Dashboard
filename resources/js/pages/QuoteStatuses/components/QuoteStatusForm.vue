<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import type { QuoteStatusFormData } from '@/types/forms/quote-statuses-form-data.js';

import QuoteStatusBasicDetailsForm from './QuoteStatusBasicDetailsForm.vue';
import QuoteStatusColourForm from './QuoteStatusColourForm.vue';

defineProps<{
    errors: Partial<Record<keyof QuoteStatusFormData, string>>;
    processing: boolean;
    isSystem: boolean;
    submitLabel: string;
    cancelHref: string;
}>();

const emit = defineEmits<{
    (event: 'submit'): void;
}>();

const form = defineModel<QuoteStatusFormData>({ required: true });
</script>

<template>
    <form class="grid gap-8" @submit.prevent="emit('submit')">
        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Basic details</h2>
            <QuoteStatusBasicDetailsForm
                v-model="form"
                :errors="errors"
                :is-system="isSystem"
            />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Colours</h2>
            <QuoteStatusColourForm v-model="form" :errors="errors" />
        </section>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">{{
                submitLabel
            }}</Button>
            <Button as-child variant="outline">
                <Link :href="cancelHref">Cancel</Link>
            </Button>
        </div>
    </form>
</template>
