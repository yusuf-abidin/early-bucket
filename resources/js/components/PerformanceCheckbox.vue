<script setup lang="ts">

defineProps<{
    log: { id: number; is_achieved: number | null | boolean} | null;
    loading: boolean;
}>();

defineEmits<{
    (e: 'toggle') : void
    (e: 'hover', event: MouseEvent) : void
    (e: 'leave') : void
}>()
</script>

<template>
    <div
        v-if="loading"
        class="mx-auto h-6 w-6 animate-spin rounded-full border-2 border-muted border-t-foreground"
    />
        <button
            @mouseenter="$emit('hover', $event)"
            @mouseleave="$emit('leave')"
            v-else-if="log?.is_achieved === 1"
            class="hover:cursor-pointer mx-auto flex h-6 w-6 items-center justify-center rounded border-2 border-green-500 bg-green-500 text-white transition-opacity hover:opacity-80"
            @click="$emit('toggle')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <button
            @mouseenter="$emit('hover', $event)"
            @mouseleave="$emit('leave')"
            v-else-if="log?.is_achieved === 0"
            class="hover:cursor-pointer mx-auto flex h-6 w-6 items-center justify-center rounded border-2 border-red-500 bg-red-500 text-white transition-opacity hover:opacity-80"
            @click="$emit('toggle')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <button
            @mouseenter="$emit('hover', $event)"
            @mouseleave="$emit('leave')"
            v-else
            class="hover:cursor-pointer mx-auto flex h-6 w-6 items-center justify-center rounded border-2 border-slate-200 bg-transparent transition-colors hover:border-green-400 hover:bg-green-50"
            @click="$emit('toggle')"
        />
</template>

<style scoped></style>
