<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { Button } from '@/components/ui/button';
import { create, destroy, edit, send } from '@/routes/notification-broadcasts';
import type {
    NotificationBroadcast,
    Pagination as PaginationMeta,
    PermissionsMeta,
} from '@/types';

interface Props {
    notificationBroadcasts: {
        data: NotificationBroadcast[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: PaginationMeta;
    };
    permissions: PermissionsMeta;
}

defineProps<Props>();

const deleteDialogOpen = ref(false);
const selectedBroadcastId = ref<number | null>(null);
const deleteProcessing = ref(false);

function sendNow(notificationBroadcast: NotificationBroadcast): void {
    router.post(send(notificationBroadcast.id).url);
}

function requestDestroy(id: number): void {
    selectedBroadcastId.value = id;
    deleteDialogOpen.value = true;
}

function remove(): void {
    if (selectedBroadcastId.value === null) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(destroy(selectedBroadcastId.value).url, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
            selectedBroadcastId.value = null;
        },
    });
}
</script>

<template>
    <div>
        <IndexHeader
            title="Notifications"
            :create-href="create().url"
            create-label="New notification"
            :can-create="permissions.can_create"
        />

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400">
                    <th>Title</th>
                    <th>Audience</th>
                    <th>Sent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="notificationBroadcast in notificationBroadcasts.data"
                    :key="notificationBroadcast.id"
                >
                    <td>{{ notificationBroadcast.title }}</td>
                    <td>{{ notificationBroadcast.audience_type }}</td>
                    <td>{{ notificationBroadcast.sent_at ?? 'Not sent' }}</td>
                    <td class="space-x-2 text-right">
                        <Button
                            v-if="!notificationBroadcast.sent_at"
                            variant="secondary"
                            as-child
                        >
                            <Link :href="edit(notificationBroadcast.id).url"
                                >Edit</Link
                            >
                        </Button>
                        <Button
                            v-if="!notificationBroadcast.sent_at"
                            variant="default"
                            @click="sendNow(notificationBroadcast)"
                        >
                            Send
                        </Button>
                        <Button
                            variant="destructive"
                            @click="requestDestroy(notificationBroadcast.id)"
                        >
                            Delete
                        </Button>
                    </td>
                </tr>
            </tbody>
        </table>

        <Pagination
            :meta="notificationBroadcasts.meta"
            :links="notificationBroadcasts.links"
            resource-label="notifications"
        />

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete notification?"
            description="This cannot be undone."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="remove"
        />
    </div>
</template>
