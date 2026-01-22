<script setup lang="ts">
import HorizontalImageList from '@/components/HorizontalImageList.vue';
import TaskTable from '@/components/TaskTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    BreadcrumbItem,
    Category,
    Task,
    User,
    UserSummary,
} from '@/types';
import { Head } from '@inertiajs/vue3';
import tasksRoute from '@/routes/tasks';


const props = defineProps<{
    tasks: Task[];
    users: User[];
    categories: Category[];
    users_summary: UserSummary[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pending Matter',
        href: tasksRoute.index().url
    },
];

</script>

<template>
    <Head title="Pending Matter" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <HorizontalImageList :users_summary="props.users_summary" />

            <TaskTable
                :tasksData="props.tasks"
                :usersData="props.users"
                :categories="categories"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
