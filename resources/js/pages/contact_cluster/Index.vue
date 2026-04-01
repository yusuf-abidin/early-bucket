<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, EditContactPayload, Regional } from '@/types';
import ContactClusterTable from '@/components/ContactClusterTable.vue';
import { ref } from 'vue';
import FormContactClusterModal from '@/components/FormContactClusterModal.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontak Cluster',
        href: '#',
    },
];

defineProps<{
    regionals: Regional[];
}>();

const formContactIsOpen = ref<boolean>(false);
const editingContact = ref<EditContactPayload | null>(null);
</script>

<template>
    <Head title="Kontak Cluster" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <ContactClusterTable
                :regionals="regionals"
                v-model:form-contact-is-open="formContactIsOpen"
                v-model:edit-contact-payload="editingContact"
            />

            <FormContactClusterModal
                v-model:form-contact-is-open="formContactIsOpen"
                v-model:editContactPayload="editingContact"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
