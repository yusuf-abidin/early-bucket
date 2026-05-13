<script setup lang="ts">
import CompleteTaskDialog from '@/components/CompleteTaskDialog.vue';
import DialogDeleteTask from '@/components/DialogDeleteTask.vue';
import HorizontalImageList from '@/components/HorizontalImageList.vue';
import TaskFormModal from '@/components/TaskFormModal.vue';
import TaskTable from '@/components/TaskTable.vue';
import type {
    BreadcrumbItem,
    Category,
    Task,
    User,
    UserSummary,
} from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import RlqhLayout from '@/layouts/RlqhLayout.vue';
import rlqh from '@/routes/rlqh';

const props = defineProps<{
    tasks: Task[];
    users: User[];
    categories: Category[];
    users_summary: UserSummary[];
    scope?: string;
}>();

const dialogDeleteTaskIsOpen = ref<boolean>(false);
const dialogResolveTaskIsOpen = ref<boolean>(false);
const selectedData = ref<Task | null>(null);
const formIsOpen = ref<boolean>(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pending Matter',
        href: rlqh.tasks.index().url,
    },
];
</script>

<template>
    <Head title="Pending Matter" />

    <RlqhLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <HorizontalImageList :users_summary="props.users_summary" :scope="props.scope ?? 'rlqh'"/>

            <TaskTable
                :tasksData="props.tasks"
                :usersData="props.users"
                :categories="categories"
                v-model:dialog-delete-task="dialogDeleteTaskIsOpen"
                v-model:dialog-resolve-task="dialogResolveTaskIsOpen"
                v-model:selected-data="selectedData"
                v-model:form-is-open="formIsOpen"
            />

            <DialogDeleteTask
                v-model:dialog-delete-task-is-open="dialogDeleteTaskIsOpen"
                v-model:selected-data="selectedData"
            />

            <CompleteTaskDialog
                v-model:complete-task-is-open="dialogResolveTaskIsOpen"
                v-model:selected-data="selectedData"
            />

            <TaskFormModal
                v-model:form-is-open="formIsOpen"
                v-model:selected-data="selectedData"
                :users-data="props.users"
                :categories="props.categories"
                :scope="props.scope"
            />
        </div>
    </RlqhLayout>
</template>

<style scoped></style>
