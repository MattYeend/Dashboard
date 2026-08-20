<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { enable, disable } from '@/routes/system/maintenance';

const props = defineProps<{
    maintenanceMode: boolean;
    bypassUrl?: string;
}>();

const form = useForm({
    secret: null as string | null,
});
const showConfirm = ref(false);

const submit = () => {
    if (props.maintenanceMode) {
        form.post(disable().url, {
            onSuccess: () => {
                showConfirm.value = false;
            },
        });
    } else {
        form.post(enable().url, {
            onSuccess: () => {
                showConfirm.value = false;
            },
        });
    }
};
</script>

<template>
    <div class="space-y-2 rounded border border-gray-500 p-4">
        <h2 class="font-medium text-gray-300">Maintenance mode</h2>
        <p class="text-sm text-gray-400">
            Currently {{ maintenanceMode ? 'enabled' : 'disabled' }}.
        </p>
        <Button variant="outline" @click="showConfirm = true">
            {{ maintenanceMode ? 'Disable' : 'Enable' }} maintenance mode
        </Button>

        <p
            v-if="bypassUrl"
            class="rounded border border-gray-500 p-2 text-sm break-all text-gray-300"
        >
            You will be locked out too. Visit this link now to regain access:
            <a :href="bypassUrl" class="underline">{{ bypassUrl }}</a>
        </p>

        <ConfirmDialog
            v-model:open="showConfirm"
            :title="
                maintenanceMode
                    ? 'Disable maintenance mode?'
                    : 'Enable maintenance mode?'
            "
            :description="
                maintenanceMode
                    ? 'The application will become accessible to all users again.'
                    : 'Everyone, including you, will be locked out. A bypass link will be shown after enabling - visit it immediately.'
            "
            @confirm="submit"
        />
    </div>
</template>
