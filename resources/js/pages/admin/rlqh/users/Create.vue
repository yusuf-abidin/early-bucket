<script setup lang="ts">
import FormUser from '@/components/FormUser.vue';
import admin from '@/routes/admin';
import type { BreadcrumbItem, Color } from '@/types';
import { Head } from '@inertiajs/vue3';
import RlqhLayout from '@/layouts/RlqhLayout.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: admin.rlqh.users.index().url,
    },
    {
        title: 'Users',
        href: admin.rlqh.users.index().url,
    },
    {
        title: 'Create',
        href: admin.rlqh.users.index().url,
    },
];

defineProps<{
    colors: Color[];
    scope: string;
}>();
</script>

<template>
    <Head title="Users" />

    <RlqhLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <FormUser :colors="colors" mode="create" :scope="scope" />
        </div>
    </RlqhLayout>
</template>

<style scoped></style>
