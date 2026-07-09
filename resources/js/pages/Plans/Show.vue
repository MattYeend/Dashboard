<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
    edit as plansEdit,
    destroy as plansDestroy,
    index as plansIndex,
} from '@/routes/plans';
import type { Plan, PermissionsMeta } from '@/types';
import PlanAuditDetails from './components/PlanAuditDetails.vue';
import PlanBasicDetails from './components/PlanBasicDetails.vue';
import PlanPricingDetails from './components/PlanPricingDetails.vue';

interface Props {
    plan: Plan;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.plan?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.plan?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(plansDestroy.url(props.plan.id), {
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
                    {{ plan.name }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="plansIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="plansEdit.url(plan.id)"
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
                <PlanBasicDetails :plan="plan" />
                <PlanPricingDetails :plan="plan" />
                <PlanAuditDetails :plan="plan" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete plan"
            description="This plan will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
