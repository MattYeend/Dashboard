<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { clear } from '@/routes/system/cache';

const form = useForm({});
const showConfirm = ref(false);

const submit = () => {
    form.post(clear().url, {
        onSuccess: () => {
            showConfirm.value = false;
        },
    });
};
</script>

<template>
    <div class="space-y-2 rounded border border-gray-500 p-4">
        <h2 class="font-medium text-gray-300">Cache</h2>
        <Button variant="outline" @click="showConfirm = true">
            Clear application cache
        </Button>

        <ConfirmDialog
            v-model:open="showConfirm"
            title="Clear cache?"
            description="This clears the application cache for all users."
            @confirm="submit"
        />
    </div>
</template>
