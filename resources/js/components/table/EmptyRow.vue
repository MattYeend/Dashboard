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
            class="px-6 py-4 text-center text-sm text-gray-400"
        >
            {{ message }}
        </td>
    </tr>
</template>
