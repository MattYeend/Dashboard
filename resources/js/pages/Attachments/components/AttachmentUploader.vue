<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    attachableType: string;
    attachableId: number;
}>();

const emit = defineEmits<{ uploaded: [] }>();

const allowedMimeTypes = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/csv',
];
const maxSizeBytes = 10 * 1024 * 1024;

const fileInput = ref<HTMLInputElement | null>(null);
const clientError = ref<string | null>(null);

const form = useForm({
    attachable_type: props.attachableType,
    attachable_id: props.attachableId,
    file: null as File | null,
});

function onFileSelected(event: Event) {
    clientError.value = null;
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    if (!file) {
        return;
    }

    if (!allowedMimeTypes.includes(file.type)) {
        clientError.value = 'That file type is not permitted.';

        return;
    }

    if (file.size > maxSizeBytes) {
        clientError.value = 'The file may not be larger than 10MB.';

        return;
    }

    form.file = file;
    submit();
}

function submit() {
    form.post('/attachments', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();

            if (fileInput.value) {
                fileInput.value.value = '';
            
                emit('uploaded');
            }
        },
    });
}
</script>

<template>
    <div>
        <Input ref="fileInput" type="file" @change="onFileSelected" />
        <p v-if="clientError" class="text-sm text-red-400">{{ clientError }}</p>
        <InputError :message="form.errors.file" />
    </div>
</template>
