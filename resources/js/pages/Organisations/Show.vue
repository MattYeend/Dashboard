<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import BulkInviteForm from '@/pages/Organisations/components/BulkInviteForm.vue';
import InviteMemberForm from '@/pages/Organisations/components/InviteMemberForm.vue';
import OrganisationAuditDetails from '@/pages/Organisations/components/OrganisationAuditDetails.vue';
import OrganisationBasicDetails from '@/pages/Organisations/components/OrganisationBasicDetails.vue';
import OrganisationMembersList from '@/pages/Organisations/components/OrganisationMembersList.vue';
import {
    edit as organisationsEdit,
    settings as organisationsSettings,
    billing as organisationsBilling,
    destroy as organisationsDestroy,
    index as organisationsIndex,
} from '@/routes/organisations';
import type {
    Organisation,
    OrganisationMembership,
    PermissionsMeta,
    RoleOption,
} from '@/types';

interface Props {
    organisation: Organisation;
    permissions_meta: PermissionsMeta;
    can_switch: boolean;
    can_remove_member: boolean;
    can_view_billing: boolean;
    can_invite: boolean;
    assignable_roles: RoleOption[];
    members: OrganisationMembership[];
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);
const switchProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(organisationsDestroy.url(props.organisation.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

function switchToOrganisation(): void {
    switchProcessing.value = true;

    router.post(
        `/organisations/${props.organisation.id}/switch`,
        {},
        {
            onFinish: () => {
                switchProcessing.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    Organisation
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <button
                        v-if="props.can_switch"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                        :disabled="switchProcessing"
                        @click="switchToOrganisation"
                    >
                        Switch to this organisation
                    </button>
                    <Link
                        :href="organisationsIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="organisationsEdit.url(props.organisation.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="organisationsSettings.url(props.organisation.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Settings
                    </Link>
                    <Link
                        v-if="props.can_view_billing"
                        :href="organisationsBilling.url(props.organisation.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Billing
                    </Link>
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
                <OrganisationBasicDetails :organisation="organisation" />
                <OrganisationMembersList
                    :organisation-id="organisation.id"
                    :members="members"
                    :can-remove="can_remove_member"
                />
                <InviteMemberForm
                    v-if="can_invite"
                    :organisation-id="organisation.id"
                    :assignable-roles="assignable_roles"
                />
                <BulkInviteForm
                    v-if="can_invite"
                    :organisation-id="organisation.id"
                    :assignable-roles="assignable_roles"
                />
                <OrganisationAuditDetails :organisation="organisation" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete organisation"
            description="This organisation will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
