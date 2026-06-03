<script setup lang="ts">
import type { Contact } from '@/types';
defineProps<{ contact: Contact }>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-grey-900 text-lg leading-6 font-medium">
                Address Details
            </h3>
        </div>
        <div class="border-grey-200 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Address</dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.address ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">City</dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.city ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">
                        Postal Code
                    </dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.postal_code ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Country</dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.country ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
