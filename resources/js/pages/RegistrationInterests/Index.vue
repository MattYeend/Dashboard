<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { RegistrationInterest, Pagination as PaginationMeta } from '@/types';
import FilterBar from '@/components/FilterBar.vue';
import IndexHeader from '@/components/IndexHeader.vue';
import Pagination from '@/components/Pagination.vue';
import ResourceTable from '@/components/ResourceTable.vue';
import {
    index as registrationInterestsIndex,
    show as registrationInterestShow,
    destroy as registrationInterestDestroy,
} from '@/routes/registration-interests';
import { bulkDelete } from '@/routes/registration-interests/bulk';

defineProps<{
    interests: RegistrationInterest[];
    meta: PaginationMeta;
    permissions: {
        can_create: boolean;
        can_view_any: boolean;
    };
}>();

const selected = ref<number[]>([]);
const confirmingDeleteId = ref<number | null>(null);
const confirmingBulkDelete = ref(false);

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'company', label: 'Company', sortable: true },
    { key: 'created_at', label: 'Submitted', sortable: true },
];
</script>

<template>
    <Head title="Registration interests" />

    <div class="p-6">
        <IndexHeader
            title="Registration interests"
            :can-create="false"
        />

        <FilterBar :index-route="registrationInterestsIndex()" />

        <ResourceTable
            :items="interests"
            :columns="columns"
            selectable
            v-model:selected="selected"
        >
            <template #cell-created_at="{ item }">
                {{ new Date(item.created_at).toLocaleDateString('en-GB') }}
            </template>

            <template #actions="{ item }">
                <a :href="registrationInterestShow(item.id).url" class="underline">View</a>
                <button
                    v-if="!item.deleted_at"
                    type="button"
                    class="ml-3 underline"
                    @click="confirmingDeleteId = item.id"
                >
                    Delete
                </button>
            </template>

            <template #bulk-actions>
                <button
                    type="button"
                    class="underline"
                    @click="confirmingBulkDelete = true"
                >
                    Delete selected
                </button>
            </template>
        </ResourceTable>

        <Pagination :meta="meta" resource-label="registration interests" />

        <ConfirmDialog
            :open="confirmingDeleteId !== null"
            title="Delete registration interest?"
            message="This will move the record to trash."
            :action="confirmingDeleteId ? registrationInterestDestroy(confirmingDeleteId).url : ''"
            method="delete"
            @close="confirmingDeleteId = null"
        />

        <ConfirmDialog
            :open="confirmingBulkDelete"
            title="Delete selected interests?"
            message="This will move the selected records to trash."
            :action="bulkDelete().url"
            method="post"
            :body="{ ids: selected }"
            @close="confirmingBulkDelete = false"
        />
    </div>
</template>