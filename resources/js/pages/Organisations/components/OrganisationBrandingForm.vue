<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as attachmentsStore } from '@/routes/attachments';
import { updateSettings as organisationsUpdateSettings } from '@/routes/organisations';
import type { OrganisationSettings } from '@/types';

interface Props {
    organisationId: number;
    settings: OrganisationSettings | null;
    logoDownloadUrl: string | null;
}

const props = defineProps<Props>();

const uploading = ref(false);
const uploadError = ref<string | null>(null);

const form = useForm({
    accent_colour: props.settings?.accent_colour ?? '',
    logo_attachment_id: props.settings?.logo_attachment_id ?? null,
});

function submit(): void {
    form.patch(organisationsUpdateSettings.url(props.organisationId), {
        preserveScroll: true,
    });
}

async function uploadLogo(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    uploading.value = true;
    uploadError.value = null;

    const payload = new FormData();
    payload.append('attachable_type', 'organisation');
    payload.append('attachable_id', String(props.organisationId));
    payload.append('file', file);

    try {
        const response = await axios.post(attachmentsStore.url(), payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        form.logo_attachment_id = response.data.id;
        submit();
    } catch {
        uploadError.value =
            'That file could not be uploaded. Please try a different file.';
    } finally {
        uploading.value = false;
        input.value = '';
    }
}

function removeLogo(): void {
    form.logo_attachment_id = null;
    submit();
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Branding</h2>

        <div class="mb-4 space-y-2">
            <a
                v-if="logoDownloadUrl"
                :href="logoDownloadUrl"
                class="block text-sm underline"
                target="_blank"
                rel="noopener"
            >
                View current logo
            </a>
            <p v-else class="text-sm text-gray-400">No logo uploaded yet.</p>

            <div class="flex items-center gap-3">
                <Label for="logo">Upload logo</Label>
                <Input
                    id="logo"
                    type="file"
                    accept="image/png,image/jpeg,image/svg+xml"
                    :disabled="uploading"
                    @change="uploadLogo"
                />
            </div>
            <p v-if="uploading" class="text-sm text-gray-400">Uploading…</p>
            <p v-if="uploadError" class="text-sm text-red-600">
                {{ uploadError }}
            </p>

            <button
                v-if="logoDownloadUrl"
                type="button"
                class="text-sm text-red-600 hover:text-red-900"
                @click="removeLogo"
            >
                Remove logo
            </button>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <Label for="accent_colour">Accent colour</Label>
                <Input
                    id="accent_colour"
                    v-model="form.accent_colour"
                    type="text"
                    placeholder="#4F46E5"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.accent_colour" />
            </div>

            <Button type="submit" :disabled="form.processing">
                Save branding
            </Button>
        </form>
    </div>
</template>
