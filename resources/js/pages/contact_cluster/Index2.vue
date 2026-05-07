<script setup lang="ts">
import {
    Area,
    Branch,
    BreadcrumbItem,
    Category,
    EditContactPayload,
    Regional,
    StcTlContact,
} from '@/types';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FormContactClusterModal from '@/components/FormContactClusterModal.vue';
import FormContactStcTlModal from '@/components/FormContactStcTlModal.vue';
import BaganWilayahCluster from '@/components/BaganWilayahCluster.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontak Cluster',
        href: '#',
    },
];

defineProps<{
    regionals: Regional[];
    categories: Category[];
}>();

const formContactIsOpen = ref<boolean>(false);
const editingContact = ref<EditContactPayload | null>(null);

const formStcTlIsOpen = ref<boolean>(false);
const editingStcTl = ref<{
    branch: Branch;
    role: 'STC' | 'TL';
    contact: StcTlContact | null;
} | null>(null);

const selectedRegion = ref<{
    regional: Regional | null;
    area: Area | null;
    branch: Branch | null;
} | null>(null);
</script>

<template>
    <Head title="Kontak Cluster" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <BaganWilayahCluster
                :regionals="regionals"
                v-model:form-contact-is-open="formContactIsOpen"
                v-model:edit-contact-payload="editingContact"
                v-model:form-stc-tl-is-open="formStcTlIsOpen"
                v-model:edit-stc-tl-contact="editingStcTl"
                v-model:selected-region="selectedRegion"
            />
            <FormContactClusterModal
                v-model:form-contact-is-open="formContactIsOpen"
                v-model:editContactPayload="editingContact"
            />

            <FormContactStcTlModal
                v-model:edit-stc-tl-contact="editingStcTl"
                v-model:form-stc-tl-is-open="formStcTlIsOpen"
                :categories="categories"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
