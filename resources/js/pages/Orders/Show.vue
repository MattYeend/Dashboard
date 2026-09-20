<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ActivityTimeline from '@/pages/Activities/components/ActivityTimeline.vue';
import OrderAuditDetails from '@/pages/Orders/components/OrderAuditDetails.vue';
import OrderBasicDetails from '@/pages/Orders/components/OrderBasicDetails.vue';
import OrderTagsDetails from '@/pages/Orders/components/OrderTagsDetails.vue';
import {
    edit as ordersEdit,
    destroy as ordersDestroy,
    index as ordersIndex,
} from '@/routes/orders';
import type { Order, PermissionsMeta, ActivityPermissionsMeta } from '@/types';

interface Props {
    order: Order;
    permissions_meta: PermissionsMeta;
    activity_permissions_meta: ActivityPermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(ordersDestroy.url(props.order.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ order.title }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Link
                        :href="ordersIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="ordersEdit.url(props.order.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
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
                <OrderBasicDetails :order="order" />
                <OrderAuditDetails :order="order" />
                <OrderTagsDetails :order="order" />
                <ActivityTimeline
                    activityable-type="order"
                    :activityable-id="order.id"
                    :can-create="activity_permissions_meta.can_create"
                    :can-export="activity_permissions_meta.can_export"
                />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete order"
            description="This order will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
