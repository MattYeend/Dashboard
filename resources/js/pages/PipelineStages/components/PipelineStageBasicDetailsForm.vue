<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface PipelineStageFormData {
    title: string;
    description: string | null;
    position: number;
}

interface Props {
    errors: Partial<InertiaFormProps<PipelineStageFormData>['errors']>;
}

defineProps<Props>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    required: true,
});
const position = defineModel<number>('position', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title"
                >Title <span class="text-destructive">*</span></Label
            >
            <Input
                id="title"
                v-model="title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter stage title"
            />
            <InputError :message="errors.title" />
        </div>

        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="description ?? ''"
                class="mt-1 block w-full"
                @update:model-value="
                    description = $event === '' ? null : String($event)
                "
            />
            <InputError :message="errors.description" />
        </div>

        <div>
            <Label for="position">Position</Label>
            <Input
                id="position"
                :model-value="position"
                type="number"
                min="0"
                class="mt-1 block w-full"
                @update:model-value="
                    position = $event === '' ? 0 : Number($event)
                "
            />
            <InputError :message="errors.position" />
        </div>
    </div>
</template>