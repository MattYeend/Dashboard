<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface PipelineStageFormData {
    background_colour: string;
    text_colour: string;
}

interface Props {
    errors: Partial<InertiaFormProps<PipelineStageFormData>['errors']>;
}

defineProps<Props>();

const backgroundColour = defineModel<string>('backgroundColour', {
    required: true,
});
const textColour = defineModel<string>('textColour', { required: true });
</script>

<template>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <Label for="background_colour">Background colour</Label>
            <div class="mt-1 flex items-center gap-2">
                <input
                    id="background_colour"
                    v-model="backgroundColour"
                    type="color"
                    class="h-9 w-12 rounded border border-gray-500"
                />
                <Input v-model="backgroundColour" type="text" class="w-full" />
            </div>
            <InputError :message="errors.background_colour" />
        </div>

        <div>
            <Label for="text_colour">Text colour</Label>
            <div class="mt-1 flex items-center gap-2">
                <input
                    id="text_colour"
                    v-model="textColour"
                    type="color"
                    class="h-9 w-12 rounded border border-gray-500"
                />
                <Input v-model="textColour" type="text" class="w-full" />
            </div>
            <InputError :message="errors.text_colour" />
        </div>
    </div>
</template>