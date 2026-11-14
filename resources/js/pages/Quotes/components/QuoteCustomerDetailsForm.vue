<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';

import { Label } from '@/components/ui/label';
import { quotableOptions } from '@/routes/quotes';

import type { QuoteFormData, SelectOption } from '@/types/forms/quotes-form-data';

const props = defineProps<{
    errors: Partial<Record<keyof QuoteFormData, string>>;
    quotableTypes: SelectOption[];
}>();

const form = defineModel<QuoteFormData>({ required: true });

const options = ref<SelectOption[]>([]);
const loading = ref(false);
let requestToken = 0;

async function loadOptions(type: string): Promise<void> {
    options.value = [];

    if (
        type === '' ||
        !props.quotableTypes.some((item) => item.value === type)
    ) {
        return;
    }

    const token = ++requestToken;
    loading.value = true;

    try {
        const response = await fetch(quotableOptions.url(type), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (response.ok && token === requestToken) {
            options.value = (await response.json()) as SelectOption[];
        }
    } finally {
        if (token === requestToken) {
            loading.value = false;
        }
    }
}

watch(
    () => form.value.quotable_type,
    (type, previous) => {
        if (previous !== undefined && previous !== type) {
            form.value.quotable_id = null;
        }

        void loadOptions(type);
    },
);

onMounted(() => {
    void loadOptions(form.value.quotable_type);
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="quotable_type">Customer type</Label>
            <select
                id="quotable_type"
                v-model="form.quotable_type"
                class="h-9 rounded-md border border-input px-3 text-sm"
                required
            >
                <option value="" disabled>Select a type</option>
                <option
                    v-for="type in quotableTypes"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </option>
            </select>
            <p v-if="errors.quotable_type" class="text-sm text-destructive">
                {{ errors.quotable_type }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="quotable_id">Customer</Label>
            <select
                id="quotable_id"
                v-model="form.quotable_id"
                class="h-9 rounded-md border border-input px-3 text-sm"
                :disabled="loading || options.length === 0"
                required
            >
                <option :value="null" disabled>
                    {{ loading ? 'Loading...' : 'Select a customer' }}
                </option>
                <option
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <p v-if="errors.quotable_id" class="text-sm text-destructive">
                {{ errors.quotable_id }}
            </p>
        </div>
    </div>
</template>
