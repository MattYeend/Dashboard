<script setup lang="ts">
import type { Contact } from '@/types';

interface Props {
    contact: Contact;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-grey-900 text-lg leading-6 font-medium">
                Audit Details
            </h3>
        </div>
        <div class="border-grey-200 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Created At
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.created_at }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Created By
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.creator?.name ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Last Updated At
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.updated_at }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Last Updated By
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.updater?.name ?? '—' }}
                    </dd>
                </div>
                <template v-if="contact.deleted_at">
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-grey-500 text-sm font-medium">
                            Deleted At
                        </dt>
                        <dd
                            class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                        >
                            {{ contact.deleted_at }}
                        </dd>
                    </div>
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-grey-500 text-sm font-medium">
                            Deleted By
                        </dt>
                        <dd
                            class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                        >
                            {{ contact.deleter?.name ?? '—' }}
                        </dd>
                    </div>
                </template>
                <template v-if="contact.restored_at">
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-grey-500 text-sm font-medium">
                            Restored At
                        </dt>
                        <dd
                            class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                        >
                            {{ contact.restored_at }}
                        </dd>
                    </div>
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-grey-500 text-sm font-medium">
                            Restored By
                        </dt>
                        <dd
                            class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                        >
                            {{ contact.restorer?.name ?? '—' }}
                        </dd>
                    </div>
                </template>
            </dl>
        </div>
    </div>
</template>
