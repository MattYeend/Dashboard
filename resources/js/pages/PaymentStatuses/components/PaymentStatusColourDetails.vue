<script setup lang="ts">
import type { PaymentStatus } from '@/types';

defineProps<{
    paymentStatus: PaymentStatus;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-3">
        <div>
            <dt class="text-sm text-muted-foreground">Background colour</dt>
            <dd class="font-mono">{{ paymentStatus.background_colour }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Text colour</dt>
            <dd class="font-mono">{{ paymentStatus.text_colour }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Preview</dt>
            <dd>
                <span
                    class="inline-block rounded-md border-2 px-3 py-1 text-sm font-medium"
                    :style="{
                        color: paymentStatus.text_colour,
                        borderColor: paymentStatus.background_colour,
                    }"
                >
                    {{ paymentStatus.title }}
                </span>
            </dd>
        </div>
    </dl>
</template>
