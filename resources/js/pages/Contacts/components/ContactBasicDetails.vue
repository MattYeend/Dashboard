<script setup lang="ts">
import type { Contact } from '@/types';
defineProps<{ contact: Contact }>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-gray-300 text-lg leading-6 font-medium">
                Basic Details
            </h3>
        </div>
        <div class="border-gray-500 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-gray-400 text-sm font-medium">
                        Email Address
                    </dt>
                    <dd
                        class="text-gray-300 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.email ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-gray-400 text-sm font-medium">Phone</dt>
                    <dd
                        class="text-gray-300 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ contact.phone ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
