<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    current_page: number;
    last_page: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}>();

const go = (url: string | null) => {
    if (!url) return;
    router.get(url, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// helper
const pages = computed(() => {
    const window = 2;
    const pages: number[] = [];

    const start = Math.max(1, props.current_page - window);
    const end   = Math.min(props.last_page, props.current_page + window);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});
</script>

<template>
    <div class="flex items-center gap-1 flex-wrap">

        <!-- PREV -->
        <button
            @click="go(links[0]?.url)"
            :disabled="!links[0]?.url"
            class="px-2 py-1 text-sm rounded border"
        >
            Prev
        </button>

        <!-- FIRST -->
        <button
            v-if="pages[0] > 1"
            @click="go(links[1]?.url)"
            class="px-2 py-1 text-sm rounded border"
        >
            1
        </button>

        <span v-if="pages[0] > 2">…</span>

        <!-- WINDOW -->
        <button
            v-for="page in pages"
            :key="page"
            @click="go(links[page]?.url)"
            class="px-2 py-1 text-sm rounded border"
            :class="page === current_page
                ? 'bg-primary text-primary-foreground'
                : 'bg-muted hover:bg-muted/80'"
        >
            {{ page }}
        </button>

        <span v-if="pages.at(-1)! < last_page - 1">…</span>

        <!-- LAST -->
        <button
            v-if="pages.at(-1)! < last_page"
            @click="go(links[last_page]?.url)"
            class="px-2 py-1 text-sm rounded border"
        >
            {{ last_page }}
        </button>

        <!-- NEXT -->
        <button
            @click="go(links[links.length - 1]?.url)"
            :disabled="!links[links.length - 1]?.url"
            class="px-2 py-1 text-sm rounded border"
        >
            Next
        </button>
    </div>
</template>
