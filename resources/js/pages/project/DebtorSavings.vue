<script setup lang="ts">
import { BreadcrumbItem, Category, Task, User, UserSummary } from '@/types';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HorizontalImageList from '@/components/HorizontalImageList.vue';
import TaskTable from '@/components/TaskTable.vue';
import { ref } from 'vue';
import DialogDeleteTask from '@/components/DialogDeleteTask.vue';
import CompleteTaskDialog from '@/components/CompleteTaskDialog.vue';
import TaskFormModal from '@/components/TaskFormModal.vue';
import TaskProgressBar from '@/components/TaskProgressBar.vue';

const props = defineProps<{
    debtor_savings: Task[];
    users: User[];
    categories: Category[];
    users_summary: UserSummary[];
    task_stats: Object;
}>();

const dialogDeleteTaskIsOpen = ref<boolean>(false);
const dialogResolveTaskIsOpen = ref<boolean>(false);
const selectedData = ref<Task | null>(null);
const formIsOpen = ref<boolean>(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Debitur Menabung',
        href: '#',
    },
];
</script>

<template>
    <Head title="Debitur Menabung" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <TaskProgressBar :task-stats="task_stats"/>
            <HorizontalImageList :users_summary="props.users_summary" />
            <TaskTable
                :tasks-data="props.debtor_savings"
                :users-data="props.users"
                :categories="categories"
                v-model:dialog-delete-task="dialogDeleteTaskIsOpen"
                v-model:dialog-resolve-task="dialogResolveTaskIsOpen"
                v-model:selected-data="selectedData"
                v-model:form-is-open="formIsOpen"
            />

            <DialogDeleteTask
                v-model:dialog-delete-task-is-open="dialogDeleteTaskIsOpen"
                v-model:selected-data="selectedData"
                mode="debtor_savings"
            />

            <CompleteTaskDialog
                v-model:completeTaskIsOpen="dialogResolveTaskIsOpen"
                v-model:selected-data="selectedData"
                mode="debtor_savings"
            />

            <TaskFormModal
                v-model:formIsOpen="formIsOpen"
                v-model:selected-data="selectedData"
                :users-data="props.users"
                :categories="props.categories"
                :mode="'debtor_savings'"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
