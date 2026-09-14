<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { RoleOption } from '@/types';

interface Props {
    organisationId: number;
    assignableRoles: RoleOption[];
}

interface SeatImpact {
    current_seats: number;
    projected_seats: number;
    current_total: string;
    projected_total: string;
}

interface PreviewResult {
    invited: string[];
    skipped: string[];
    invalid: string[];
    seat_impact: SeatImpact | null;
}

const props = defineProps<Props>();

const emailList = ref('');
const previewResult = ref<PreviewResult | null>(null);
const previewProcessing = ref(false);
const previewError = ref<string | null>(null);
const confirmDialogOpen = ref(false);

const form = useForm({
    emails: [] as string[],
    invited_role: '',
});

const parsedEmails = computed<string[]>(() =>
    emailList.value
        .split(/[\n,]+/)
        .map((email) => email.trim())
        .filter((email) => email !== ''),
);

async function preview(): Promise<void> {
    previewProcessing.value = true;
    previewError.value = null;
    previewResult.value = null;

    try {
        const response = await axios.post<PreviewResult>(
            `/organisations/${props.organisationId}/invitations/bulk/preview`,
            {
                emails: parsedEmails.value,
                invited_role: form.invited_role,
            },
        );

        previewResult.value = response.data;
    } catch {
        previewError.value =
            'Could not preview this batch. Check a role is selected and try again.';
    } finally {
        previewProcessing.value = false;
    }
}

function requestConfirm(): void {
    confirmDialogOpen.value = true;
}

function submit(): void {
    form.emails = parsedEmails.value;

    form.post(`/organisations/${props.organisationId}/invitations/bulk`, {
        onSuccess: () => {
            form.reset();
            emailList.value = '';
            previewResult.value = null;
            confirmDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="bulk_invited_role">Role</Label>
            <Select v-model="form.invited_role">
                <SelectTrigger id="bulk_invited_role">
                    <SelectValue placeholder="Select a role" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="role in props.assignableRoles"
                        :key="role.id"
                        :value="role.name"
                    >
                        {{ role.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div>
            <Label for="emails">Email addresses</Label>
            <Textarea
                id="emails"
                v-model="emailList"
                rows="6"
                placeholder="One email per line, or comma separated"
            />
            <InputError :message="form.errors.emails" />
        </div>

        <Button
            type="button"
            :disabled="
                previewProcessing ||
                parsedEmails.length === 0 ||
                !form.invited_role
            "
            @click="preview"
        >
            Preview
        </Button>

        <p v-if="previewError" class="text-sm text-red-600">
            {{ previewError }}
        </p>

        <div v-if="previewResult" class="space-y-2 text-sm">
            <p>{{ previewResult.invited.length }} will be invited:</p>
            <p class="text-gray-400">
                {{ previewResult.invited.join(', ') || '-' }}
            </p>

            <p>
                {{ previewResult.skipped.length }} already active, will be
                skipped:
            </p>
            <p class="text-gray-400">
                {{ previewResult.skipped.join(', ') || '-' }}
            </p>

            <p>{{ previewResult.invalid.length }} invalid, will be skipped:</p>
            <p class="text-gray-400">
                {{ previewResult.invalid.join(', ') || '-' }}
            </p>

            <p v-if="previewResult.seat_impact">
                Seats: {{ previewResult.seat_impact.current_seats }} →
                {{ previewResult.seat_impact.projected_seats }}
                ({{ previewResult.seat_impact.current_total }} →
                {{ previewResult.seat_impact.projected_total }})
            </p>

            <Button
                type="button"
                :disabled="previewResult.invited.length === 0"
                @click="requestConfirm"
            >
                Invite {{ previewResult.invited.length }} people
            </Button>
        </div>

        <ConfirmDialog
            v-model:open="confirmDialogOpen"
            title="Send invitations"
            :description="`This will invite ${previewResult?.invited.length ?? 0} people to the organisation.`"
            confirm-label="Invite"
            :processing="form.processing"
            @confirm="submit"
        />
    </div>
</template>
