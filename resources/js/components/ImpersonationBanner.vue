<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { stop as stopImpersonating } from '@/routes/users/impersonate';

const page = usePage();

const isImpersonating = computed(() => Boolean(page.props.isImpersonating));
const impersonatorName = computed(
    () => page.props.impersonatorName as string | null,
);

function returnToOwnAccount(): void {
    router.post(stopImpersonating.url());
}
</script>

<template>
    <div
        v-if="isImpersonating"
        class="flex items-center justify-between border-b border-amber-500 px-4 py-2 text-sm text-amber-400"
    >
        <span>Viewing as this user on behalf of {{ impersonatorName }}.</span>
        <button
            type="button"
            class="rounded border border-amber-500 px-3 py-1 text-amber-400 hover:text-amber-300"
            @click="returnToOwnAccount"
        >
            Return to my account
        </button>
    </div>
</template>
