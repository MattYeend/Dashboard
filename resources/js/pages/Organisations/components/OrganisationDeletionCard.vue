<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import TypeToConfirmDialog from '@/components/TypeToConfirmDialog.vue';

interface Props {
    organisation: { id: number; name: string };
}

const props = defineProps<Props>();

const showConfirm = ref(false);

const form = useForm({
    confirmation_name: '',
});

function requestDeletion(): void {
    form.post(`/organisations/${props.organisation.id}/data-privacy/request-deletion`, {
        onSuccess: () => {
            showConfirm.value = false;
        },
    });
}
</script>

<template>
    <div class="border-gray-500 rounded border p-4">
        <h2 class="text-sm font-semibold text-gray-300">Delete this organisation</h2>
        <p class="mt-1 text-xs text-gray-400">
            Permanently deletes {{ organisation.name }} and everything belonging to it. This cannot be undone once the retention window has passed.
        </p>
        <button
            type="button"
            class="border-gray-500 mt-3 rounded border px-3 py-1.5 text-xs text-gray-300"
            @click="showConfirm = true"
        >
            Delete organisation
        </button>

        <TypeToConfirmDialog
            v-if="showConfirm"
            v-model="form.confirmation_name"
            :expected-value="organisation.name"
            :processing="form.processing"
            :error="form.errors.confirmation_name"
            title="Delete organisation"
            :description="`Type '${organisation.name}' to confirm permanent deletion.`"
            @confirm="requestDeletion"
            @cancel="showConfirm = false"
        />
    </div>
</template>