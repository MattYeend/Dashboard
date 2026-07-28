<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { computed } from 'vue';
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

const HEX_COLOUR_PATTERN = /^#[0-9a-f]{6}$/i;

function isValidHexColour(value: string): boolean {
    return HEX_COLOUR_PATTERN.test(value);
}

const backgroundColourSwatch = computed<string>({
    get: () =>
        isValidHexColour(backgroundColour.value)
            ? backgroundColour.value
            : '#e5e7eb',
    set: (value) => {
        backgroundColour.value = value;
    },
});

const textColourSwatch = computed<string>({
    get: () =>
        isValidHexColour(textColour.value) ? textColour.value : '#111827',
    set: (value) => {
        textColour.value = value;
    },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <Label for="background_colour">Background colour</Label>
            <div class="mt-1 flex items-center gap-2">
                <input
                    id="background_colour"
                    v-model="backgroundColourSwatch"
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
                    v-model="textColourSwatch"
                    type="color"
                    class="h-9 w-12 rounded border border-gray-500"
                />
                <Input v-model="textColour" type="text" class="w-full" />
            </div>
            <InputError :message="errors.text_colour" />
        </div>
    </div>
</template>
