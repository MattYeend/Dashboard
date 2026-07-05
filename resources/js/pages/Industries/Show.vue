<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { Industry, PermissionsMeta } from '@/types';
import IndustryAuditDetails from './components/IndustryAuditDetails.vue';
import IndustryBasicDetails from './components/IndustryBasicDetails.vue';
import {
    edit as industriesEdit,
    destroy as industriesDestroy,
    index as industriesIndex,
} from '@/routes/industries';

interface Props {
    industry: Industry;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.industry?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.industry?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(industriesDestroy.url(props.industry.id), {
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
                    {{ industry.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="industriesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="industriesEdit.url(industry.id)"
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
                <IndustryBasicDetails :industry="industry" />
                <IndustryAuditDetails :industry="industry" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete industry"
            description="This industry will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>