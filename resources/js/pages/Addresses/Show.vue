<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import AddressAuditDetails from '@/pages/Addresses/components/AddressAuditDetails.vue';
import AddressBasicDetails from '@/pages/Addresses/components/AddressBasicDetails.vue';
import {
    edit as addressesEdit,
    destroy as addressesDestroy,
    index as addressesIndex,
} from '@/routes/addresses';
import type { Address } from '@/types';

interface Props {
    address: Address;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(addressesDestroy.url(props.address.id), {
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
                <h1 class="text-2xl font-semibold text-gray-300">Address</h1>
                <div class="space-x-2">
                    <Link
                        :href="addressesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="addressesEdit.url(props.address.id)"
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
                <AddressBasicDetails :address="address" />
                <AddressAuditDetails :address="address" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete address"
            description="This address will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
