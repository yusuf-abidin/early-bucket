<script setup lang="ts">
import {
    type BreadcrumbItem,
    LaravelPaginator,
    Task,
    UserSummary,
} from '@/types';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import taskRoute from '@/routes/tasks';
import HorizontalImageList from '@/components/HorizontalImageList.vue';
import TaskHistoryFilter from '@/components/TaskHistoryFilter.vue';
import TaskHistoryTable from '@/components/TaskHistoryTable.vue';
import { provide, ref, watch } from 'vue';
import TaskHistoryPagination from '@/components/TaskHistoryPagination.vue';
import DialogDeleteTask from '@/components/DialogDeleteTask.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pending Matter',
        href: taskRoute.index().url,
    },
    {
        title: 'History',
        href: taskRoute.history().url,
    },
];

const props = defineProps<{
    tasks_history: LaravelPaginator<Task>;
    users_summary: UserSummary[];
    users: { id: number; name: string }[];
}>();

const loadVisibleColumns = () => {
    const stored = localStorage.getItem('taskHistoryTableColumns');
    return stored
        ? JSON.parse(stored)
        : {
              task: true,
              assignedUser: true,
              createdDate: true,
              dueDate: true,
              resolvedDate: true,
              category: true,
              notes: true,
          };
};

const visibleColumns = ref(loadVisibleColumns());

watch(
    visibleColumns,
    (newVal) => {
        localStorage.setItem('taskHistoryTableColumns', JSON.stringify(newVal));
    },
    { deep: true },
);

provide('visibleColumns', visibleColumns);

const dialogDeleteTaskIsOpen = ref<boolean>(false);
const selectedTask = ref<Task | null>(null);
</script>

<template>
    <Head title="Riwayat Pending Matter" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <HorizontalImageList
                :users_summary="props.users_summary"
                :mode="'history'"
            />
            <TaskHistoryFilter :users="props.users" />
            <TaskHistoryTable
                :tasks="props.tasks_history.data"
                v-model:dialog-delete-task-is-open="dialogDeleteTaskIsOpen"
                v-model:selected-data="selectedTask"
            />
            <TaskHistoryPagination
                :links="props.tasks_history.links"
                :current_page="props.tasks_history.current_page"
                :last_page="props.tasks_history.last_page"
            />

            <DialogDeleteTask
                :mode="'pending_matter'"
                v-model:dialog-delete-task-is-open="dialogDeleteTaskIsOpen"
                v-model:selected-data="selectedTask"
            />
        </div>
    </AppLayout>
</template>
