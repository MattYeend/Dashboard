<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import { destroy as apiTokensDestroy } from '@/routes/api-tokens';
import type { ApiToken } from '@/types';
import ApiTokenForm from './components/ApiTokenForm.vue';
import ApiTokenList from './components/ApiTokenList.vue';
import NewTokenReveal from './components/NewTokenReveal.vue';

defineProps<{
    tokens: ApiToken[];
    abilities: Record<string, string>;
}>();

const page = usePage<{ flash: { plainTextToken?: string } }>();
const plainTextToken = computed(() => page.props.flash?.plainTextToken ?? null);

const revokeDialogOpen = ref(false);
const selectedToken = ref<ApiToken | null>(null);
const revokeProcessing = ref(false);

function requestRevoke(token: ApiToken): void {
    selectedToken.value = token;
    revokeDialogOpen.value = true;
}

function revoke(): void {
    if (selectedToken.value === null) {
        return;
    }

    revokeProcessing.value = true;

    router.delete(apiTokensDestroy.url(selectedToken.value.id), {
        preserveScroll: true,
        onFinish: () => {
            revokeProcessing.value = false;
            revokeDialogOpen.value = false;
            selectedToken.value = null;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader title="API Tokens" :can-create="false" />

            <NewTokenReveal v-if="plainTextToken" :token="plainTextToken" />

            <div class="mt-6 space-y-6">
                <ApiTokenForm :abilities="abilities" />

                <ApiTokenList
                    :tokens="tokens"
                    :abilities="abilities"
                    @revoke="requestRevoke"
                />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="revokeDialogOpen"
            title="Revoke token"
            :description="`Token \&quot;${selectedToken?.name}\&quot; will be permanently revoked. This cannot be undone.`"
            confirm-label="Revoke"
            :processing="revokeProcessing"
            @confirm="revoke"
        />
    </div>
</template>
