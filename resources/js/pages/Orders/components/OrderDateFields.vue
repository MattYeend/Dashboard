<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<string, string>>;
}>();

const orderedAt = defineModel<string | null>('orderedAt', { default: null });
const dueAt = defineModel<string | null>('dueAt', { default: null });
const completedAt = defineModel<string | null>('completedAt', {
    default: null,
});
</script>

<template>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <Label for="ordered_at">Ordered At</Label>
            <Input
                id="ordered_at"
                :model-value="orderedAt ?? ''"
                type="datetime-local"
                class="mt-1 block w-full"
                @update:model-value="orderedAt = ($event as string) || null"
            />
            <InputError :message="errors.ordered_at" />
        </div>
        <div>
            <Label for="due_at">Due At</Label>
            <Input
                id="due_at"
                :model-value="dueAt ?? ''"
                type="datetime-local"
                class="mt-1 block w-full"
                @update:model-value="dueAt = ($event as string) || null"
            />
            <InputError :message="errors.due_at" />
        </div>
        <div>
            <Label for="completed_at">Completed At</Label>
            <Input
                id="completed_at"
                :model-value="completedAt ?? ''"
                type="datetime-local"
                class="mt-1 block w-full"
                @update:model-value="completedAt = ($event as string) || null"
            />
            <InputError :message="errors.completed_at" />
        </div>
    </div>
</template>
