<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { store } from '@/routes/backups';

const form = useForm({
    only_db: false,
});

const showConfirm = ref(false);

function submit(): void {
    form.post(store().url, {
        onSuccess: () => {
            showConfirm.value = false;
        },
    });
}
</script>

<template>
    <div>
        <Button variant="outline" @click="showConfirm = true">
            Create backup
        </Button>

        <ConfirmDialog
            v-model:open="showConfirm"
            title="Create a new backup?"
            description="This runs the backup process now. It may take a few minutes depending on database size."
            confirm-label="Create"
            :processing="form.processing"
            @confirm="submit"
        />
    </div>
</template>
