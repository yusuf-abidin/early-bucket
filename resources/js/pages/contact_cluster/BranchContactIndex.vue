<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BranchContact, BreadcrumbItem, Regional } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import BranchContactCard from '@/components/BranchContactCard.vue';
import { Button } from '@/components/ui/button';
import { PlusIcon, UsersIcon, MapPinIcon } from 'lucide-vue-next';
import FormBranchContactModal from '@/components/FormBranchContactModal.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontak BM',
        href: '#',
    },
];

const selectedRegional = ref<Regional | null>(null);

const showedContacts = computed(() => {
    return selectedRegional.value?.branch_contacts ?? [];
});

const props = defineProps<{
    contacts: Regional[];
}>();

const formBranchContactIsOpen = ref<boolean>(false);
const selectedContact = ref<BranchContact | null>(null);

const handleAdd = () => {
    formBranchContactIsOpen.value = true;
};

const handleEdit = (contact: BranchContact) => {
    selectedContact.value = contact;
    formBranchContactIsOpen.value = true;
}

watch(
    () => props.contacts,
    (newContacts) => {
        if (!selectedRegional.value) return;

        const updatedRegional = newContacts.find(r => r.id === selectedRegional.value?.id)
        if (updatedRegional) {
            selectedRegional.value = updatedRegional;
        }
    },
    { deep: true}
)

const clickRegional = (regional: Regional) => {
    selectedRegional.value = regional;
};
</script>

<template>
    <Head title="Kontak BM" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex h-full flex-col gap-4 sm:flex-row">
                <div
                    class="flex shrink-0 flex-col gap-0.5 rounded-xl border p-3 sm:w-44"
                >
                    <p class="px-3 pt-1 pb-2 text-xs font-medium uppercase">
                        Regional
                    </p>
                    <button
                        v-for="regional in contacts"
                        :key="regional.id"
                        @click="clickRegional(regional)"
                        :class="[
                            'w-full rounded-lg px-3 py-2 text-left text-sm transition-colors',
                            selectedRegional?.id === regional.id
                                ? 'bg-slate-100 font-medium dark:bg-slate-800 dark:text-slate-100'
                                : 'hover:bg-slate-50 dark:hover:bg-slate-800/50',
                        ]"
                    >
                        {{ regional.name }}
                    </button>
                </div>

                <div
                    class="flex min-w-0 flex-1 flex-col gap-4 rounded-xl border p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-medium dark:text-slate-100">
                                {{ selectedRegional?.name ?? 'Pilih Regional' }}
                            </h2>
                            <span
                                class="rounded-full bg-slate-100 px-2 py-0.5 text-xs dark:bg-slate-700"
                            >
                                {{ showedContacts.length }} kontak
                            </span>
                        </div>
                        <Button
                            v-if="selectedRegional"
                            size="sm"
                            @click="handleAdd"
                            class="cursor-pointer gap-1.5"
                        >
                            <PlusIcon class="h-4 w-4" />
                            Tambah Kontak
                        </Button>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="showedContacts.length === 0 && selectedRegional"
                        class="flex flex-1 flex-col items-center justify-center gap-3 py-16"
                    >
                        <UsersIcon class="h-10 w-10" />
                        <p class="text-sm font-medium text-slate-700">Belum ada kontak</p>
                        <p class="text-center text-xs text-slate-600">
                            Regional ini belum memiliki kontak BM terdaftar.
                        </p>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="handleAdd"
                            class="mt-1 gap-1.5"
                        >
                            <PlusIcon class="h-4 w-4" />
                            Tambah Kontak Pertama
                        </Button>
                    </div>

                    <div
                        v-if="!selectedRegional"
                        class="flex flex-1 flex-col items-center justify-center gap-3 py-16"
                    >
                        <MapPinIcon class="h-10 w-10" />
                        <p class="text-sm font-medium">Pilih Regional</p>
                        <p class="text-center text-xs text-slate-600">
                            Silakan pilih salah satu regional untuk melihat
                            kontak BM yang terdaftar.
                        </p>
                    </div>

                    <!-- Grid kontak -->
                    <div
                        v-else
                        class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                    >
                        <BranchContactCard
                            @edit="handleEdit"
                            v-for="contact in showedContacts"
                            :key="contact.id"
                            :contact="contact"
                        />
                    </div>
                </div>
            </div>

            <FormBranchContactModal
                v-model:form-contact-is-open="formBranchContactIsOpen"
                v-model:selected-contact="selectedContact"
                v-model:selected-regional="selectedRegional"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
