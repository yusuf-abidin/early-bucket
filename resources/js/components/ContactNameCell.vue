<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    name?: string | null;
}

const props = defineProps<Props>();

function avatarColor(name?: string | null): string {
    const palette = [
        'bg-violet-100 text-violet-700',
        'bg-sky-100 text-sky-700',
        'bg-teal-100 text-teal-700',
        'bg-rose-100 text-rose-700',
        'bg-amber-100 text-amber-700',
        'bg-indigo-100 text-indigo-700',
    ];
    if (!name) return palette[0];
    return palette[name.charCodeAt(0) % palette.length];
}

function initials(name?: string | null): string {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    return parts.length === 1
        ? parts[0][0].toUpperCase()
        : (parts[0][0] + parts[1][0]).toUpperCase();
}

const color = computed(() => avatarColor(props.name));
const abbr = computed(() => initials(props.name));
</script>

<template>
    <div v-if="name" class="flex items-center gap-2">
        <span :class="`flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold ${color}`">
            {{ abbr }}
        </span>
        <span class="text-sm font-medium leading-tight">{{ name }}</span>
    </div>
    <span v-else class="text-center">-</span>
</template>

<style scoped></style>
