<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { Label, PermissionsMeta } from '@/types';
import LabelAuditDetails from './components/LabelAuditDetails.vue';
import LabelBasicDetails from './components/LabelBasicDetails.vue';
import LabelColourDetails from './components/LabelColourDetails.vue';
import {
    edit as labelsEdit,
    destroy as labelsDestroy,
    index as labelsIndex,
} from '@/routes/labels';

interface Props {
    label: Label;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.label?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.label?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(labelsDestroy.url(props.label.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ label.name }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="labelsIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="labelsEdit.url(label.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="permissions_meta.can_create"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <LabelBasicDetails :label="label" />
                <LabelColourDetails :label="label" />
                <LabelAuditDetails :label="label" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete label"
            description="This label will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
