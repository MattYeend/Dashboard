<script setup lang="ts">
import type { QuoteStatus } from '@/types';

defineProps<{
    quoteStatus: QuoteStatus;
}>();
</script>

<template>
    <dl class="grid gap-4 sm:grid-cols-3">
        <div>
            <dt class="text-sm text-muted-foreground">Background colour</dt>
            <dd class="font-mono">{{ quoteStatus.background_colour }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Text colour</dt>
            <dd class="font-mono">{{ quoteStatus.text_colour }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted-foreground">Preview</dt>
            <dd>
                <span
                    class="inline-block rounded-md border-2 px-3 py-1 text-sm font-medium"
                    :style="{ color: quoteStatus.text_colour, borderColor: quoteStatus.background_colour }"
                >
                    {{ quoteStatus.title }}
                </span>
            </dd>
        </div>
    </dl>
</template>