<script setup lang="ts">
import type { Contact } from '@/types';

interface Props {
    contact: Contact;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Email address</dt>
                <dd class="text-sm">{{ contact.email ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Phone</dt>
                <dd class="text-sm">{{ contact.phone ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>
