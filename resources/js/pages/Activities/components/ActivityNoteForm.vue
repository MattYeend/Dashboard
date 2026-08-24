<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { nullIfBlank } from '@/lib/forms';
import { store as activitiesStore } from '@/routes/activities';

interface Props {
    activityableType: string;
    activityableId: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    created: [];
}>();

const form = useForm({
    activityable_type: props.activityableType,
    activityable_id: props.activityableId,
    type: 'note',
    description: '',
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).post(activitiesStore.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('description');
            emit('created');
        },
    });
}
</script>

<template>
    <form class="flex flex-col gap-2" @submit.prevent="submit">
        <Textarea
            v-model="form.description"
            placeholder="Add a note..."
            rows="2"
            class="border-gray-500 text-gray-300"
        />
        <InputError :message="form.errors.description" />
        <div class="flex justify-end">
            <Button
                type="submit"
                size="sm"
                :disabled="form.processing || !form.description.trim()"
            >
                Add note
            </Button>
        </div>
    </form>
</template>
