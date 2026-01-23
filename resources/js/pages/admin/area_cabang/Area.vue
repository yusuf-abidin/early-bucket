<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Area, Branch, BreadcrumbItem } from '@/types';
import admin from '@/routes/admin';
import AreaTable from '@/components/AreaTable.vue';
import { ref } from 'vue';
import FormAreaModal from '@/components/FormAreaModal.vue';
import DialogDeleteArea from '@/components/DialogDeleteArea.vue';
import FormBranchModal from '@/components/FormBranchModal.vue';
import DialogDeleteBranch from '@/components/DialogDeleteBranch.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Manajemen Area',
        href: admin.areas.index().url,
    },
];

const props = defineProps<{
    areas: Area[];
    all_areas: Area[];
}>();

const selectedArea = ref<Area | null>(null)
const selectedBranch = ref<Branch | null>(null)
const formAreaIsOpen = ref<boolean>(false)
const formBranchIsOpen = ref<boolean>(false)
const dialogDeleteAreaIsOpen = ref<boolean>(false)
const dialogDeleteBranchIsOpen = ref<boolean>(false)

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Manajemen Area" />
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <AreaTable
                :all_areas="props.all_areas"
                :areas="props.areas"
                v-model:selected-area="selectedArea"
                v-model:selected-branch="selectedBranch"
                v-model:form-area-is-open="formAreaIsOpen"
                v-model:dialog-delete-area="dialogDeleteAreaIsOpen"
                v-model:form-branch-is-open="formBranchIsOpen"
                v-model:dialog-delete-branch="dialogDeleteBranchIsOpen"
            />

            <FormAreaModal
                v-model:form-area-is-open="formAreaIsOpen"
                v-model:selected-area="selectedArea"
            />


            <FormBranchModal
                :areas="props.areas"
                v-model:selected-branch="selectedBranch"
                v-model:form-branch-is-open="formBranchIsOpen"
            />

            <DialogDeleteArea
                v-model:selected-area="selectedArea"
                v-model:dialog-delete-area="dialogDeleteAreaIsOpen"
            />

            <DialogDeleteBranch
                v-model:selected-branch="selectedBranch"
                v-model:dialog-delete-branch="dialogDeleteBranchIsOpen"
            />

        </div>
    </AppLayout>
</template>

<style scoped></style>
