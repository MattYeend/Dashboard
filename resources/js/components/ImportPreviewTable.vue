<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { ImportPreviewRow } from '@/types';

interface Props {
    columns: string[];
    rows: ImportPreviewRow[];
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-x-auto rounded-md border border-gray-700">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="border-b border-gray-700 text-left text-xs text-gray-400"
                >
                    <th class="px-3 py-2">Row</th>
                    <th
                        v-for="column in columns"
                        :key="column"
                        class="px-3 py-2 capitalize"
                    >
                        {{ column }}
                    </th>
                    <th class="px-3 py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="row in rows"
                    :key="row.row"
                    class="border-b border-gray-800 last:border-b-0"
                >
                    <td class="px-3 py-2 text-gray-400">{{ row.row }}</td>
                    <td
                        v-for="column in columns"
                        :key="column"
                        class="px-3 py-2"
                    >
                        {{ row.data[column] ?? '—' }}
                    </td>
                    <td class="px-3 py-2">
                        <Badge v-if="row.valid" variant="outline">Valid</Badge>
                        <Badge
                            v-else
                            variant="destructive"
                            :title="row.reason ?? undefined"
                        >
                            {{ row.reason }}
                        </Badge>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
