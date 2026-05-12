<script setup lang="ts">
import UserTable from '@/components/UserTable.vue';
import admin from '@/routes/admin';
import type { BreadcrumbItem, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import RlqhLayout from '@/layouts/RlqhLayout.vue';

const { users, scope } = defineProps<{
    users: User[];
    scope: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: admin.rlqh.users.index().url
    },
    {
        title: 'Users',
        href: admin.rlqh.users.index().url
    },
];
</script>

<template>
    <Head title="Users" />

    <RlqhLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <UserTable :users="users" :scope="scope"/>
        </div>
    </RlqhLayout>
</template>

<style scoped></style>
