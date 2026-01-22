<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Color } from '@/types';
import admin from '@/routes/admin';
import FormUser from '@/components/FormUser.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: admin.users.index().url,
    },
    {
        title: 'Users',
        href: admin.users.index().url,
    },
    {
        title: 'Create',
        href: admin.users.index().url,
    },
];

defineProps<{
    colors: Color[];
}>();
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <FormUser :colors="colors" mode="create"/>
        </div>
    </AppLayout>
</template>

<style scoped></style>
