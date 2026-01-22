<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem, User } from '@/types';
import admin from '@/routes/admin';
import UserTable from "@/components/UserTable.vue";

const {users} = defineProps<{
    users: User[]
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
];
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <UserTable :users="users"/>
        </div>
    </AppLayout>
</template>

<style scoped></style>
