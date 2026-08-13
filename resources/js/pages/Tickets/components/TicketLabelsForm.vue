<script setup lang="ts">
import { Label } from '@/components/ui/label';
import type { Label as LabelOption } from '@/types';

interface Props {
    availableLabels: LabelOption[];
}

defineProps<Props>();

const labelIds = defineModel<number[]>('labelIds', { required: true });

function toggle(id: number, checked: boolean): void {
    labelIds.value = checked
        ? [...labelIds.value, id]
        : labelIds.value.filter((existing) => existing !== id);
}
</script>

<template>
    <div>
        <Label>Labels</Label>
        <div class="mt-1 flex flex-wrap gap-3">
            <label
                v-for="label in availableLabels"
                :key="label.id"
                class="flex items-center gap-1.5 text-sm"
            >
                <input
                    type="checkbox"
                    :checked="labelIds.includes(label.id)"
                    @change="
                        toggle(
                            label.id,
                            ($event.target as HTMLInputElement).checked,
                        )
                    "
                />
                {{ label.name }}
            </label>
            <p v-if="!availableLabels.length" class="text-sm text-gray-400">
                No labels available.
            </p>
        </div>
    </div>
</template>
