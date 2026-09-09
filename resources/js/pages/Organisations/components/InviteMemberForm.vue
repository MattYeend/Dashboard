<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    organisationId: number;
}

const props = defineProps<Props>();

const form = useForm({
    email: '',
});

function submit(): void {
    form.post(`/organisations/${props.organisationId}/invitations`, {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div>
            <Label for="email">Invite by email</Label>
            <Input id="email" v-model="form.email" type="email" required />
            <InputError :message="form.errors.email" />
        </div>

        <Button type="submit" :disabled="form.processing">Send invitation</Button>
    </form>
</template>
