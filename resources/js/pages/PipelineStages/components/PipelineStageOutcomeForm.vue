<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

interface PipelineStageFormData {
    is_won: boolean;
    is_lost: boolean;
}

interface Props {
    errors: Partial<InertiaFormProps<PipelineStageFormData>['errors']>;
}

defineProps<Props>();

const isWon = defineModel<boolean>('isWon', { required: true });
const isLost = defineModel<boolean>('isLost', { required: true });

type Outcome = 'open' | 'won' | 'lost';

function currentOutcome(): Outcome {
    if (isWon.value) {
        return 'won';
    }

    if (isLost.value) {
        return 'lost';
    }

    return 'open';
}

function setOutcome(outcome: Outcome): void {
    isWon.value = outcome === 'won';
    isLost.value = outcome === 'lost';
}
</script>

<template>
    <div>
        <Label>Outcome</Label>
        <div class="mt-1 flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input
                    type="radio"
                    name="pipeline-stage-outcome"
                    value="open"
                    :checked="currentOutcome() === 'open'"
                    @change="setOutcome('open')"
                />
                Open
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input
                    type="radio"
                    name="pipeline-stage-outcome"
                    value="won"
                    :checked="currentOutcome() === 'won'"
                    @change="setOutcome('won')"
                />
                Won
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input
                    type="radio"
                    name="pipeline-stage-outcome"
                    value="lost"
                    :checked="currentOutcome() === 'lost'"
                    @change="setOutcome('lost')"
                />
                Lost
            </label>
        </div>
        <InputError :message="errors.is_won ?? errors.is_lost" />
    </div>
</template>
