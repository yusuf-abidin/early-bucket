<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';
import type { BreadcrumbItem, Color, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import FormUser from '@/components/FormUser.vue';

defineProps<{
    user: User;
    colors: Color[];
}>();

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
        title: 'Edit',
        href: admin.users.index().url,
    },
];
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <FormUser :user="user" mode="edit" :colors="colors" />
        </div>
    </AppLayout>
</template>

<style scoped></style>
