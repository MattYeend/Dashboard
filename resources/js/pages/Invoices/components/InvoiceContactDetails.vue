<script setup lang="ts">
import type { Invoice } from '@/types';

interface Props {
    invoice: Invoice;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Contact</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Phone</dt>
                <dd class="text-sm">{{ invoice.contact?.phone ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Email</dt>
                <dd class="text-sm">{{ invoice.contact?.email ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Address</dt>
                <dd class="text-sm">{{ invoice.contact?.address ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">City</dt>
                <dd class="text-sm">{{ invoice.contact?.city ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Postal code</dt>
                <dd class="text-sm">
                    {{ invoice.contact?.postal_code ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Country</dt>
                <dd class="text-sm">{{ invoice.contact?.country ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>
