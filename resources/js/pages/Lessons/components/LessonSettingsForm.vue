<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<string, string>>;
}>();

const durationMinutes = defineModel<number | null>('durationMinutes', {
    required: true,
});
const isPreview = defineModel<boolean>('isPreview', { required: true });
const publishedAt = defineModel<string>('publishedAt', { required: true });

/**
 * Bridge the nullable duration to the Input component, which does not
 * accept null. A blank field maps back to null.
 */
const durationInput = computed<string | number>({
    get: () => durationMinutes.value ?? '',
    set: (value) => {
        const parsed = value === '' ? null : Number(value);

        durationMinutes.value =
            parsed === null || Number.isNaN(parsed) ? null : parsed;
    },
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="grid gap-2">
            <Label for="duration">Duration (minutes)</Label>
            <Input
                id="duration"
                v-model="durationInput"
                type="number"
                min="1"
                max="1440"
            />
            <InputError :message="errors.duration_minutes" />
        </div>

        <div class="grid gap-2">
            <Label for="published-at">Publish at</Label>
            <Input
                id="published-at"
                v-model="publishedAt"
                type="datetime-local"
            />
            <InputError :message="errors.published_at" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is-preview"
                v-model="isPreview"
                type="checkbox"
                class="size-4"
            />
            <Label for="is-preview">Free preview lesson</Label>
            <InputError :message="errors.is_preview" />
        </div>
    </div>
</template>
