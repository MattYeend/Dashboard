<script setup lang="ts">
import type { Contact } from '@/types';
defineProps<{ contact: Contact }>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-grey-900">Address Details</h3>
        </div>
        <div class="border-t border-grey-200">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Address</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ contact.address ?? '—' }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">City</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ contact.city ?? '—' }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Postal Code</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ contact.postal_code ?? '—' }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Country</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ contact.country ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>