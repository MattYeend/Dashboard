<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { CommentMention } from '@/types';

interface Props {
    content: string;
    mentions?: CommentMention[];
}

const props = withDefaults(defineProps<Props>(), {
    mentions: () => [],
});

interface BodySegment {
    type: 'text' | 'mention';
    value: string;
    userId?: number;
}

function slugify(name: string): string {
    return name.toLowerCase().replace(/[^a-z0-9]/g, '');
}

const mentionsBySlug = computed<Map<string, CommentMention>>(() => {
    const map = new Map<string, CommentMention>();

    for (const mention of props.mentions) {
        map.set(slugify(mention.name), mention);
    }

    return map;
});

const segments = computed<BodySegment[]>(() => {
    const parts: BodySegment[] = [];
    const pattern = /@([a-zA-Z0-9]+)/g;
    let lastIndex = 0;
    let match: RegExpExecArray | null;

    while ((match = pattern.exec(props.content)) !== null) {
        if (match.index > lastIndex) {
            parts.push({ type: 'text', value: props.content.slice(lastIndex, match.index) });
        }

        const token = match[1].toLowerCase();
        const mention = mentionsBySlug.value.get(token);

        parts.push(
            mention
                ? { type: 'mention', value: match[0], userId: mention.id }
                : { type: 'text', value: match[0] },
        );

        lastIndex = match.index + match[0].length;
    }

    if (lastIndex < props.content.length) {
        parts.push({ type: 'text', value: props.content.slice(lastIndex) });
    }

    return parts;
});
</script>

<template>
    <p class="text-sm text-gray-300">
        <template v-for="(segment, index) in segments" :key="index">
            <Link
                v-if="segment.type === 'mention'"
                :href="`/users/${segment.userId}`"
                class="border-b border-gray-400 text-gray-300 no-underline"
            >{{ segment.value }}</Link>
            <template v-else>{{ segment.value }}</template>
        </template>
    </p>
</template>