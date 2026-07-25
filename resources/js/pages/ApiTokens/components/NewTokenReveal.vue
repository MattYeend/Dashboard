<script setup lang="ts">
defineProps<{
    token: string;
}>();

const copy = (value: string) => navigator.clipboard.writeText(value);
</script>

<template>
    <div>
        <p>Copy this token now. You will not be able to see it again.</p>
        <code>
            {{ token }}
        </code>
        <button type="button" @click="copy(token)">Copy</button>
    </div>
</template>
