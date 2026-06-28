<script setup lang="ts">
interface Props {
    colspan: number;
    message: string;
}

defineProps<Props>();
</script>

<template>
    <tr>
        <td
            :colspan="colspan"
            class="text-center text-gray-400 px-6 py-4 text-sm"
        >
            {{ message }}
        </td>
    </tr>
</template>
