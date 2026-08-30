<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';;

interface Props {
    errors: Partial<Record<'name' | 'guard_name', string>>;
}

defineProps<Props>();

const name = defineModel<string>('name', { required: true });
const guardName = defineModel<string>('guardName', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="name">Name</Label>
            <Input id="name" v-model="name" type="text" placeholder="e.g. view any permissions" />
            <InputError :message="errors.name" />
        </div>

        <div>
            <Label for="guard_name">Guard name</Label>
            <Input id="guard_name" v-model="guardName" type="text" placeholder="web" />
            <InputError :message="errors.guard_name" />
        </div>
    </div>
</template>
