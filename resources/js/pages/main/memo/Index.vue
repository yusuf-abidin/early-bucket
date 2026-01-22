<script setup lang="ts">
import type {
    BreadcrumbItem,
    Category,
    LaravelPaginator,
    Memo,
    UserSummary,
} from '@/types';
import memosRoute from '@/routes/memos';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import MemoFilter from '@/components/MemoFilter.vue';
import { provide, ref, watch } from 'vue';
import MemoTable from '@/components/MemoTable.vue';
import MemoFormModal from '@/components/MemoFormModal.vue';
import DialogDeleteMemo from '@/components/DialogDeleteMemo.vue';
import HorizontalImageMemo from '@/components/HorizontalImageMemo.vue';
import DialogCompleteMemo from '@/components/DialogCompleteMemo.vue';

defineProps<{
    users: { id: number; name: string }[];
    memos: LaravelPaginator<Memo>;
    categories: Category[];
    users_summary: UserSummary[];
    total_archive: number;
}>();

const storageTableName = 'memoTableColumns';

const selectedMemo = ref<Memo | null>(null);
const isOpen = ref<boolean>(false);
const deleteIsOpen = ref<boolean>(false);
const isOpenDialogResolveMemo = ref<boolean>(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Memo',
        href: memosRoute.index().url,
    },
];

const defaultColumns = {
    received_at: true,
    origin: true,
    reference_number: true,
    subject: true,
    category: true,
    check: true,
    document_link: true,
    due_date: true,
    assignedUser: true,
    follow_up_note: true,
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
    <Head title="Memo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <HorizontalImageMemo :users_summary="users_summary" :total_archive="total_archive"/>
            <MemoFilter
                :users="users"
                v-model:isOpen="isOpen"
                :default-columns="defaultColumns"
            />
            <MemoTable
                :memos="memos.data"
                v-model:isOpen="isOpen"
                v-model:selected-memo="selectedMemo"
                v-model:delete-is-open="deleteIsOpen"
                v-model:dialog-resolve-memo="isOpenDialogResolveMemo"
            />
            <MemoFormModal
                :users-data="users"
                :categories="categories"
                v-model:is-open="isOpen"
                v-model:selected-memo="selectedMemo"
            />

            <DialogDeleteMemo
                v-model:delete-is-open="deleteIsOpen"
                v-model:selected-memo="selectedMemo"
            />

            <DialogCompleteMemo
                v-model:dialog-resolve-memo="isOpenDialogResolveMemo"
                v-model:selected-memo="selectedMemo"
                mode="index"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
