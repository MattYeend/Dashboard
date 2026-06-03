<script setup lang="ts">
import type { User } from '@/types';

interface Props {
    user: User;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-grey-900">Audit Details</h3>
        </div>
        <div class="border-t border-grey-200">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Created At</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.created_at }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Created By</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.creator?.name ?? '—' }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Last Updated At</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.updated_at }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Last Updated By</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.updater?.name ?? '—' }}</dd>
                </div>
                <template v-if="user.deleted_at">
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-grey-500">Deleted At</dt>
                        <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.deleted_at }}</dd>
                    </div>
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-grey-500">Deleted By</dt>
                        <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.deleter?.name ?? '—' }}</dd>
                    </div>
                </template>
                <template v-if="user.restored_at">
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-grey-500">Restored At</dt>
                        <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.restored_at }}</dd>
                    </div>
                    <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-grey-500">Restored By</dt>
                        <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.restorer?.name ?? '—' }}</dd>
                    </div>
                </template>
            </dl>
        </div>
    </div>
</template>