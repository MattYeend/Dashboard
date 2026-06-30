<script setup lang="ts" generic="T extends { id: number | string }">
import { computed } from 'vue';
import EmptyRow from '@/components/table/EmptyRow.vue';

export interface ResourceTableColumn {
    key: string;
    label: string;
    class?: string;
}

interface Props {
    rows: T[];
    columns: ResourceTableColumn[];
    rowKey?: string;
    emptyMessage?: string;
    selectable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    rowKey: 'id',
    emptyMessage: 'No records found.',
    selectable: false,
});

defineSlots<
    {
        'bulk-actions'?: (props: {
            selected: Array<number | string>;
        }) => unknown;
        actions?: (props: { row: T }) => unknown;
    } & Record<`cell-${string}`, (props: { row: T }) => unknown>
>();

const selected = defineModel<Array<number | string>>('selected', {
    default: () => [],
});

const colspan = computed(
    () => props.columns.length + (props.selectable ? 1 : 0) + 1,
);

function getRowKey(row: T): number | string {
    return (row as Record<string, unknown>)[props.rowKey] as number | string;
}

function getCellValue(row: T, key: string): unknown {
    return (row as Record<string, unknown>)[key];
}

const allSelected = computed({
    get: () =>
        props.rows.length > 0 &&
        props.rows.every((row) => selected.value.includes(getRowKey(row))),
    set: (value: boolean) => {
        selected.value = value ? props.rows.map((row) => getRowKey(row)) : [];
    },
});

function isSelected(row: T): boolean {
    return selected.value.includes(getRowKey(row));
}

function toggleRow(row: T): void {
    const key = getRowKey(row);
    selected.value = isSelected(row)
        ? selected.value.filter((id) => id !== key)
        : [...selected.value, key];
}
</script>

<template>
    <div>
        <div
            v-if="selectable && selected.length"
            class="mb-2 flex flex-wrap items-center gap-3"
        >
            <p class="text-sm text-gray-400">{{ selected.length }} selected</p>
            <slot name="bulk-actions" :selected="selected" />
        </div>

        <div
            class="ring-opacity-5 overflow-x-auto shadow ring-1 ring-black sm:rounded-lg"
        >
            <table class="min-w-full divide-y divide-gray-500">
                <thead>
                    <tr>
                        <th v-if="selectable" class="px-6 py-3 text-left">
                            <input
                                v-model="allSelected"
                                type="checkbox"
                                aria-label="Select all rows"
                            />
                        </th>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-3 text-left text-xs font-medium tracking-wide text-gray-400 uppercase',
                                column.class,
                            ]"
                        >
                            {{ column.label }}
                        </th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-500">
                    <EmptyRow
                        v-if="!rows?.length"
                        :colspan="colspan"
                        :message="emptyMessage"
                    />
                    <tr v-for="row in rows" :key="getRowKey(row)">
                        <td v-if="selectable" class="px-6 py-4">
                            <input
                                :checked="isSelected(row)"
                                type="checkbox"
                                :aria-label="`Select row ${getRowKey(row)}`"
                                @change="toggleRow(row)"
                            />
                        </td>
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4 text-sm whitespace-nowrap text-gray-400"
                        >
                            <slot :name="`cell-${column.key}`" :row="row">
                                {{ getCellValue(row, column.key) ?? '—' }}
                            </slot>
                        </td>
                        <td
                            class="space-x-2 px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                        >
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
