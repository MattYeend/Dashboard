<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { upload } from '@/routes/backups';

const form = useForm<{ file: File | null }>({
    file: null,
});

function handleFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.file = target.files?.[0] ?? null;
}

function submit(): void {
    form.post(upload().url, {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <form
        class="space-y-2 rounded border border-gray-500 p-4"
        @submit.prevent="submit"
    >
        <Label for="backup-file">Import a backup file</Label>
        <input
            id="backup-file"
            type="file"
            accept=".zip"
            class="text-sm text-gray-300"
            @change="handleFileChange"
        />
        <InputError :message="form.errors.file" />

        <Button
            type="submit"
            variant="outline"
            :disabled="form.processing || !form.file"
        >
            Import
        </Button>
    </form>
</template>
