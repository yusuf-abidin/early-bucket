<script setup lang="ts">
import type { BreadcrumbItemType } from '@/types/index.d';
import RlqhSidebarLayout from '@/layouts/app/RlqhSidebarLayout.vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <RlqhSidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </RlqhSidebarLayout>
</template>
