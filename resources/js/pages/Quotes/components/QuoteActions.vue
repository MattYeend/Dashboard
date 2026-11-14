<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { accept, convert, decline, edit, send } from '@/routes/quotes';
import type { Quote } from '@/types';

import type { QuoteAbilities } from '@/types/forms';

const props = defineProps<{
    quote: Quote;
    abilities: QuoteAbilities;
}>();

function post(url: string, message: string): void {
    if (window.confirm(message)) {
        router.post(url, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Button v-if="abilities.update" as-child variant="outline">
            <Link :href="edit.url(props.quote.id)">Edit</Link>
        </Button>
        <Button
            v-if="abilities.send"
            @click="
                post(
                    send.url(props.quote.id),
                    'Send this quote? It can no longer be edited.',
                )
            "
        >
            Send
        </Button>
        <Button
            v-if="abilities.accept"
            @click="
                post(
                    accept.url(props.quote.id),
                    'Record this quote as accepted?',
                )
            "
        >
            Mark accepted
        </Button>
        <Button
            v-if="abilities.decline"
            variant="outline"
            @click="
                post(
                    decline.url(props.quote.id),
                    'Record this quote as declined?',
                )
            "
        >
            Mark declined
        </Button>
        <Button
            v-if="abilities.convert"
            @click="
                post(
                    convert.url(props.quote.id),
                    'Convert this quote into an order?',
                )
            "
        >
            Convert to order
        </Button>
    </div>
</template>
