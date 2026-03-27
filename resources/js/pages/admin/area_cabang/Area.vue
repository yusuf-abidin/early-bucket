<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Area, Branch, BreadcrumbItem, Regional } from '@/types';
import admin from '@/routes/admin';
import AreaTable from '@/components/AreaTable.vue';
import { ref } from 'vue';
import FormAreaModal from '@/components/FormAreaModal.vue';
import DialogDeleteArea from '@/components/DialogDeleteArea.vue';
import FormBranchModal from '@/components/FormBranchModal.vue';
import DialogDeleteBranch from '@/components/DialogDeleteBranch.vue';
import FormRegionalModal from '@/components/FormRegionalModal.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Manajemen Area & Cabang',
        href: admin.areas.index().url,
    },
];

const props = defineProps<{
    regionals: Regional[];
    all_regionals: Regional[];
}>();

const selectedRegional = ref<Regional | null>(null);
const selectedArea = ref<Area | null>(null);
const selectedBranch = ref<Branch | null>(null);
const formRegionalIsOpen = ref<boolean>(false);
const formAreaIsOpen = ref<boolean>(false);
const formBranchIsOpen = ref<boolean>(false);
const dialogDeleteAreaIsOpen = ref<boolean>(false);
const dialogDeleteBranchIsOpen = ref<boolean>(false);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Manajemen Area" />
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <AreaTable
                :regionals="props.regionals"
                v-model:selected-regional="selectedRegional"
                v-model:selected-area="selectedArea"
                v-model:selected-branch="selectedBranch"
                v-model:form-regional-is-open="formRegionalIsOpen"
                v-model:form-area-is-open="formAreaIsOpen"
                v-model:dialog-delete-area="dialogDeleteAreaIsOpen"
                v-model:form-branch-is-open="formBranchIsOpen"
                v-model:dialog-delete-branch="dialogDeleteBranchIsOpen"
            />

            <FormRegionalModal
                v-model:form-regional-is-open="formRegionalIsOpen"
                v-model:selected-regional="selectedRegional"
            />

            <FormAreaModal
                :regionals="props.all_regionals"
                v-model:form-area-is-open="formAreaIsOpen"
                v-model:selected-area="selectedArea"
            />

            <FormBranchModal
                :areas="props.all_regionals"
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
