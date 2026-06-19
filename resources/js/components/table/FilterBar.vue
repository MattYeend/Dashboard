<script setup lang="ts">
interface SelectOption {
    value: string;
    label: string;
}

interface FilterField {
    key: string;
    type: 'text' | 'select';
    placeholder?: string;
    options?: SelectOption[];
}

interface Props {
    fields: FilterField[];
    modelValue: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: Record<string, string>): void;
    (e: 'change'): void;
}>();

function onInput(key: string, value: string): void {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
    emit('change');
}
</script>

<template>
    <div class="mb-4 flex flex-wrap gap-2">
        <template v-for="field in fields" :key="field.key">
            <input
                v-if="field.type === 'text'"
                :value="modelValue[field.key]"
                type="text"
                class="rounded-md border px-3 py-1.5 text-sm"
                :placeholder="field.placeholder ?? ''"
                @input="
                    onInput(
                        field.key,
                        ($event.target as HTMLInputElement).value,
                    )
                "
            />
            <select
                v-else-if="field.type === 'select'"
                :value="modelValue[field.key]"
                class="rounded-md border px-3 py-1.5 text-sm"
                @change="
                    onInput(
                        field.key,
                        ($event.target as HTMLSelectElement).value,
                    )
                "
            >
                <option
                    v-for="opt in field.options ?? []"
                    :key="opt.value"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </option>
            </select>
        </template>
    </div>
</template>
