<script setup lang="ts">
import { BreadcrumbItem, LaravelPaginator, Memo } from '@/types';
import memosRoute from '@/routes/memos';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import MemoFilter from '@/components/MemoFilter.vue';
import { provide, ref, watch } from 'vue';
import MemoArchiveTable from '@/components/MemoArchiveTable.vue';
import TaskHistoryPagination from '@/components/TaskHistoryPagination.vue';
import DialogDeleteMemo from '@/components/DialogDeleteMemo.vue';
import DialogCompleteMemo from '@/components/DialogCompleteMemo.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Memo',
        href: memosRoute.index().url,
    },
    {
        title: 'Arsip',
        href: memosRoute.archive().url,
    },
];

const deleteIsOpen = ref<boolean>(false);
const isOpenDialogResolveMemo = ref<boolean>(false);

const storageTableName = 'memoArchiveTableColumns';

const props = defineProps<{
    memos: LaravelPaginator<Memo>;
    users: { id: number; name: string }[];
}>();

const selectedMemo = ref<Memo | null>(null);

const defaultColumns = {
    received_at: true,
    origin: true,
    reference_number: true,
    subject: true,
    category: true,
    document_link: true,
    assignedUser: true,
    follow_up_note: true,
    due_date: true,
    completed_at: true,
};

const loadVisibleColumns = () => {
    const stored = localStorage.getItem(storageTableName);
    return stored ? JSON.parse(stored) : { ...defaultColumns };
};

const visibleColumns = ref(loadVisibleColumns());

watch(
    visibleColumns,
    (newVal) => {
        localStorage.setItem(storageTableName, JSON.stringify(newVal));
    },
    { deep: true },
);

provide('visibleColumns', visibleColumns);
</script>

<template>
    <Head title="Arsip Memo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <MemoFilter
                :users="props.users"
                mode="history"
                :default-columns="defaultColumns"
            />

            <MemoArchiveTable
                :memos="props.memos.data"
                v-model:selected-memo="selectedMemo"
                v-model:delete-is-open="deleteIsOpen"
                v-model:dialog-resolve-memo="isOpenDialogResolveMemo"
            />
            <TaskHistoryPagination
                :current_page="props.memos.current_page"
                :last_page="props.memos.last_page"
                :links="props.memos.links"
            />
            <DialogDeleteMemo
                v-model:selected-memo="selectedMemo"
                v-model:delete-is-open="deleteIsOpen"
            />

            <DialogCompleteMemo
                v-model:selected-memo="selectedMemo"
                v-model:dialog-resolve-memo="isOpenDialogResolveMemo"
                mode="archive"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
