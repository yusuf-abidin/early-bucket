<script setup lang="ts">
interface Props {
    nip?: string | null;
}

defineProps<Props>();
</script>

<template>
    <span v-if="nip">{{ nip }}</span>
    <span v-else>-</span>
</template>

<style scoped></style>
