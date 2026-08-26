<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ActivityTimeline from '@/pages/Activities/components/ActivityTimeline.vue';
import QuickLogModal from '@/pages/InteractionLogs/components/QuickLogModal.vue';
import {
    edit as companiesEdit,
    destroy as companiesDestroy,
    index as companiesIndex,
} from '@/routes/companies';
import type {
    Company,
    PermissionsMeta,
    ActivityPermissionsMeta,
    InteractionLogPermissionsMeta,
} from '@/types';
import CompanyAuditDetails from './components/CompanyAuditDetails.vue';
import CompanyBasicDetails from './components/CompanyBasicDetails.vue';
import CompanyContactDetails from './components/CompanyContactDetails.vue';
import CompanyRegistrationDetails from './components/CompanyRegistrationDetails.vue';

interface Props {
    company: Company;
    permissions_meta: PermissionsMeta;
    activity_permissions_meta: ActivityPermissionsMeta;
    interaction_log_permissions_meta: InteractionLogPermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.company?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.company?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(companiesDestroy.url(props.company.id), {
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
                    {{ company.name }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="companiesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="companiesEdit.url(company.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <QuickLogModal
                        v-if="interaction_log_permissions_meta.can_create"
                        interactable-type="company"
                        :interactable-id="company.id"
                    />
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <CompanyBasicDetails :company="company" />
                <CompanyContactDetails :company="company" />
                <CompanyRegistrationDetails :company="company" />
                <CompanyAuditDetails :company="company" />
                <ActivityTimeline
                    activityable-type="company"
                    :activityable-id="company.id"
                    :can-create="activity_permissions_meta.can_create"
                    :can-export="activity_permissions_meta.can_export"
                />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete company"
            description="This company will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
