<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { OrganisationMembership } from '@/types';

interface Props {
    organisationId: number;
    members: OrganisationMembership[];
    canRemove: boolean;
}

const props = defineProps<Props>();

const removeDialogOpen = ref(false);
const removeProcessing = ref(false);
const selectedUserId = ref<number | null>(null);
const selectedMember = ref<OrganisationMembership | null>(null);

const removeDescription = computed(() =>
    selectedMember.value?.status === 'active'
        ? 'This member will lose access to the organisation immediately, and your billed seat count and subscription total will be updated to reflect the change.'
        : 'This member will lose access to the organisation immediately.',
);

function requestRemove(member: OrganisationMembership): void {
    selectedMember.value = member;
    selectedUserId.value = member.user_id;
    removeDialogOpen.value = true;
}

function remove(): void {
    if (selectedUserId.value === null) {
        return;
    }

    removeProcessing.value = true;

    router.delete(
        `/organisations/${props.organisationId}/members/${selectedUserId.value}`,
        {
            preserveScroll: true,
            onFinish: () => {
                removeProcessing.value = false;
                removeDialogOpen.value = false;
                selectedUserId.value = null;
                selectedMember.value = null;
            },
        },
    );
}
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="member in members"
            :key="member.id"
            class="flex items-center justify-between border-b border-gray-500 py-2"
        >
            <div>
                <p class="text-sm text-gray-300">{{ member.user?.name }}</p>
                <p class="text-xs text-gray-400">
                    {{ member.user?.email }} — {{ member.status }}
                </p>
            </div>

            <button
                v-if="canRemove"
                type="button"
                class="text-sm text-red-600 hover:text-red-900"
                @click="requestRemove(member)"
            >
                Remove
            </button>
        </div>

        <p v-if="!members.length" class="text-sm text-gray-400">
            No members yet.
        </p>
    </div>

    <ConfirmDialog
        v-model:open="removeDialogOpen"
        title="Remove member"
        :description="removeDescription"
        confirm-label="Remove"
        :processing="removeProcessing"
        @confirm="remove"
    />
</template>
