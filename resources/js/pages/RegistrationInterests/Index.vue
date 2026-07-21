<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';

import type {
    RegistrationInterest,
    Pagination as PaginationMeta,
} from '@/types';

import {
    index as registrationInterestsIndex,
    show as registrationInterestShow,
    destroy as registrationInterestDestroy,
} from '@/routes/registration-interests';

import bulk from '@/routes/registration-interests/bulk';

defineProps<{
    interests: RegistrationInterest[];
    meta: PaginationMeta;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    permissions: {
        can_create: boolean;
        can_view_any: boolean;
    };
}>();

const selected = ref<number[]>([]);

const filters = ref<Record<string, string>>({
    search: '',
});

const filterFields = [
    {
        key: 'search',
        type: 'text' as const,
        placeholder: 'Search registration interests...',
    },
];

const confirmingDelete = ref(false);
const confirmingDeleteId = ref<number | null>(null);

const confirmingBulkDelete = ref(false);
const processing = ref(false);

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'company', label: 'Company' },
    { key: 'created_at', label: 'Submitted' },
];

const applyFilters = () => {
    router.get(
        registrationInterestsIndex().url,
        filters.value,
        {
            preserveState: true,
            replace: true,
        }
    );
};

const confirmDelete = () => {
    if (confirmingDeleteId.value === null) {
        return;
    }

    processing.value = true;

    router.delete(
        registrationInterestDestroy(confirmingDeleteId.value).url,
        {
            onFinish: () => {
                processing.value = false;
                confirmingDelete.value = false;
                confirmingDeleteId.value = null;
            },
        }
    );
};

const confirmBulkDelete = () => {
    processing.value = true;

    router.post(
        bulk.delete().url,
        {
            ids: selected.value,
        },
        {
            onFinish: () => {
                processing.value = false;
                confirmingBulkDelete.value = false;
                selected.value = [];
            },
        }
    );
};

const requestDestroy = (id: number) => {
    confirmingDeleteId.value = id;
    confirmingDelete.value = true;
};
</script>

<template>
    <Head title="Registration interests" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Registration interests"
                :can-create="false"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <ResourceTable
                :rows="interests"
                :columns="columns"
                selectable
                v-model:selected="selected"
            >
                <template #cell-created_at="{ row }">
                    {{ new Date(row.created_at).toLocaleDateString('en-GB') }}
                </template>

                <template #actions="{ row }">
                    <Link :href="registrationInterestShow(row.id).url">
                        View
                    </Link>

                    <button
                        v-if="!row.deleted_at"
                        type="button"
                        class="text-red-600 hover:text-red-900"
                        @click="requestDestroy(row.id)"
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

            <Pagination
                :meta="meta"
                :links="links"
                resource-label="registration interests"
            />
        </div>

        <ConfirmDialog
            v-model:open="confirmingDelete"
            title="Delete registration interest?"
            description="This will move the record to trash."
            :processing="processing"
            @confirm="confirmDelete"
        />

        <ConfirmDialog
            v-model:open="confirmingBulkDelete"
            title="Delete selected interests?"
            description="This will move the selected records to trash."
            :processing="processing"
            @confirm="confirmBulkDelete"
        />
    </div>
</template>
