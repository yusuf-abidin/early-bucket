<script setup lang="ts">
import { Category } from '@/types';
interface Props {
    categories?: Category[];
}
const props = defineProps<Props>();
</script>

<template>
    <div
        v-if="props.categories?.length"
        class="inline-flex w-full flex-wrap gap-1 items-center align-middle"
        :title="props.categories.map((c) => c.name).join(', ')"
    >
        <span
            v-for="c in props.categories"
            :key="c.id"
            class="inline-flex flex-none items-center px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-700 dark:text-gray-200 text-gray-700 text-xs truncate max-w-[12rem]"
        >
            {{ c.name }}
        </span>
    </div>
    <template v-else>-</template>
</template>

<style scoped>

</style>
