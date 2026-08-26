<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { nullIfBlank } from '@/lib/forms';
import InteractionLogForm from './InteractionLogForm.vue';
import { store } from '@/routes/interaction-logs';

interface Props {
    interactableType: 'company' | 'contact' | 'deal';
    interactableId: number;
    contactId?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
    contactId: null,
});

const open = ref(false);

const form = useForm({
    interactable_type: props.interactableType,
    interactable_id: props.interactableId,
    type: 'call',
    subject: '',
    outcome: '',
    occurred_at: new Date().toISOString().slice(0, 10),
    notes: '',
    contact_id: props.contactId,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        outcome: nullIfBlank(data.outcome),
        notes: nullIfBlank(data.notes),
    })).post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <button
                type="button"
                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
            >
                Log interaction
            </button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Log a call or email</DialogTitle>
            </DialogHeader>

            <InteractionLogForm
                v-model:type="form.type"
                v-model:subject="form.subject"
                v-model:outcome="form.outcome"
                v-model:occurred-at="form.occurred_at"
                v-model:notes="form.notes"
                :errors="form.errors"
            />

            <DialogFooter>
                <Button variant="outline" @click="open = false">Cancel</Button>
                <Button :disabled="form.processing" @click="submit"
                    >Save</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
