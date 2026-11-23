<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { SelectOption } from '@/types';
import type {
    CampaignSegmentCriterionFormData,
    CampaignSegmentFormData,
} from '@/types/forms';

const MAX_CRITERIA = 20;

const props = defineProps<{
    form: CampaignSegmentFormData;
    errors: Partial<Record<string, string>>;
    fieldOptions: SelectOption[];
    operatorOptions: SelectOption[];
}>();

const emit = defineEmits<{
    (e: 'update:form', value: CampaignSegmentFormData): void;
}>();

function updateCriteria(criteria: CampaignSegmentCriterionFormData[]): void {
    emit('update:form', { ...props.form, criteria });
}

function addCriterion(): void {
    updateCriteria([
        ...props.form.criteria,
        {
            field: props.fieldOptions[0]?.value ?? '',
            operator: props.operatorOptions[0]?.value ?? '',
            value: '',
        },
    ]);
}

function removeCriterion(index: number): void {
    updateCriteria(props.form.criteria.filter((_, i) => i !== index));
}

function updateCriterion<K extends keyof CampaignSegmentCriterionFormData>(
    index: number,
    key: K,
    value: CampaignSegmentCriterionFormData[K],
): void {
    updateCriteria(
        props.form.criteria.map((criterion, i) =>
            i === index ? { ...criterion, [key]: value } : criterion,
        ),
    );
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <Label>Criteria</Label>
            <Button
                type="button"
                variant="outline"
                :disabled="form.criteria.length >= MAX_CRITERIA"
                @click="addCriterion"
            >
                Add criterion
            </Button>
        </div>

        <InputError :message="errors.criteria" />

        <p v-if="form.criteria.length === 0" class="text-sm text-gray-400">
            No criteria added. A segment without criteria has no audience rules.
        </p>

        <div
            v-for="(criterion, index) in form.criteria"
            :key="index"
            class="grid grid-cols-1 gap-3 rounded-lg border p-3 sm:grid-cols-4"
        >
            <div>
                <Label :for="`criteria-${index}-field`">Field</Label>
                <select
                    :id="`criteria-${index}-field`"
                    :value="criterion.field"
                    class="mt-1 block w-full rounded-md border px-3 py-2 text-sm"
                    @change="
                        updateCriterion(
                            index,
                            'field',
                            ($event.target as HTMLSelectElement).value,
                        )
                    "
                >
                    <option
                        v-for="option in fieldOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <InputError :message="errors[`criteria.${index}.field`]" />
            </div>

            <div>
                <Label :for="`criteria-${index}-operator`">Operator</Label>
                <select
                    :id="`criteria-${index}-operator`"
                    :value="criterion.operator"
                    class="mt-1 block w-full rounded-md border px-3 py-2 text-sm"
                    @change="
                        updateCriterion(
                            index,
                            'operator',
                            ($event.target as HTMLSelectElement).value,
                        )
                    "
                >
                    <option
                        v-for="option in operatorOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <InputError :message="errors[`criteria.${index}.operator`]" />
            </div>

            <div>
                <Label :for="`criteria-${index}-value`">Value</Label>
                <Input
                    :id="`criteria-${index}-value`"
                    :model-value="criterion.value"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="true, false or a number"
                    @update:model-value="
                        updateCriterion(index, 'value', $event as string)
                    "
                />
                <InputError :message="errors[`criteria.${index}.value`]" />
            </div>

            <div class="flex items-end">
                <Button
                    type="button"
                    variant="outline"
                    @click="removeCriterion(index)"
                >
                    Remove
                </Button>
            </div>
        </div>
    </div>
</template>
