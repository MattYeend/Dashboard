<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

interface Props {
    paymentStatus: 'past_due' | 'unpaid';
    billingPortalUrl: string;
}

const props = defineProps<Props>();

const message = computed(() =>
    props.paymentStatus === 'unpaid'
        ? 'Your subscription is unpaid after repeated failed charges. Update your payment details to keep your account active.'
        : 'We were unable to collect your last payment. Please update your payment details to avoid an interruption to your account.',
);
</script>

<template>
    <div
        class="flex items-start gap-3 rounded-md border border-amber-500/50 p-4 text-sm"
    >
        <AlertTriangle class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" />
        <div class="flex-1">
            <p class="font-medium text-amber-500">Payment issue</p>
            <p class="mt-1 text-gray-300">{{ message }}</p>
        </div>
        <Button as="a" :href="billingPortalUrl" variant="outline" size="sm">
            Update payment details
        </Button>
    </div>
</template>
