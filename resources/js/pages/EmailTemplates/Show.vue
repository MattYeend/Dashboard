<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import EmailTemplateAuditDetails from '@/pages/EmailTemplates/components/EmailTemplateAuditDetails.vue';
import EmailTemplateBasicDetails from '@/pages/EmailTemplates/components/EmailTemplateBasicDetails.vue';
import EmailTemplateContentDetails from '@/pages/EmailTemplates/components/EmailTemplateContentDetails.vue';
import {
    destroy as emailTemplatesDestroy,
    edit as emailTemplatesEdit,
    index as emailTemplatesIndex,
} from '@/routes/email-templates';
import type { EmailTemplate, PermissionsMeta } from '@/types';

interface Props {
    emailTemplate: EmailTemplate;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(emailTemplatesDestroy.url(props.emailTemplate.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ emailTemplate.name }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="emailTemplatesIndex.url()">Back</Link>
                    </Button>
                    <Button v-if="permissions_meta.can_update" as-child>
                        <Link
                            :href="
                                emailTemplatesEdit.url(props.emailTemplate.id)
                            "
                            >Edit</Link
                        >
                    </Button>
                    <button
                        v-if="permissions_meta.can_delete"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <EmailTemplateBasicDetails :email-template="emailTemplate" />
                <EmailTemplateContentDetails :email-template="emailTemplate" />
                <EmailTemplateAuditDetails :email-template="emailTemplate" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete email template"
            description="This email template will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>