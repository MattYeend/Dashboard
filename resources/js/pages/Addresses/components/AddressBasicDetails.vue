<script setup lang="ts">
import type { Address } from '@/types';

interface Props {
    address: Address;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Address details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Line one</dt>
                <dd class="text-sm">{{ address.address_line_one }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Line two</dt>
                <dd class="text-sm">{{ address.address_line_two ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Town</dt>
                <dd class="text-sm">{{ address.town ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">City</dt>
                <dd class="text-sm">{{ address.city }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">County</dt>
                <dd class="text-sm">{{ address.county ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Postcode</dt>
                <dd class="text-sm">{{ address.postcode ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Country</dt>
                <dd class="text-sm">{{ address.country }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Primary</dt>
                <dd class="text-sm">{{ address.is_primary ? 'Yes' : 'No' }}</dd>
            </div>
        </dl>
    </div>
</template>
