<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
    edit as orderStatusesEdit,
    destroy as orderStatusesDestroy,
    index as orderStatusesIndex,
} from '@/routes/order-statuses';
import type { OrderStatus, PermissionsMeta } from '@/types';
import OrderStatusAuditDetails from './components/OrderStatusAuditDetails.vue';
import OrderStatusBasicDetails from './components/OrderStatusBasicDetails.vue';
import OrderStatusColourDetails from './components/OrderStatusColourDetails.vue';

interface Props {
    orderStatus: OrderStatus;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.orderStatus?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.orderStatus?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(orderStatusesDestroy.url(props.orderStatus.id), {
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
                    {{ orderStatus.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="orderStatusesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="orderStatusesEdit.url(orderStatus.id)"
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
                <OrderStatusBasicDetails :order-status="orderStatus" />
                <OrderStatusColourDetails :order-status="orderStatus" />
                <OrderStatusAuditDetails :order-status="orderStatus" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete order status"
            description="This order status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
