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
            class="text-centre text-gray-500 px-6 py-4 text-sm"
        >
            {{ message }}
        </td>
    </tr>
</template>
