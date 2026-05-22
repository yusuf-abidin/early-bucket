<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    BranchContact,
    BreadcrumbItem,
    DbmscContact,
    EditBranchContactPayload,
    Regional,
} from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import BranchContactCard from '@/components/BranchContactCard.vue';
import { Button } from '@/components/ui/button';
import { PlusIcon, UsersIcon, MapPinIcon, Search } from 'lucide-vue-next';
import FormBranchContactModal from '@/components/FormBranchContactModal.vue';
import DbmscContactCard from '@/components/DbmscContactCard.vue';
import { Input } from '@/components/ui/input';
import { debouncedWatch } from '@vueuse/core';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kontak BM',
        href: '#',
    },
];

const props = defineProps<{
    contacts: Regional[];
}>();

const selectedRegional = ref<Regional | null>(null);
const selectedBranchContact = ref<BranchContact | null>(null);

const showedContacts = computed(() => {
    return selectedRegional.value?.branch_contacts ?? [];
});

const formBranchContactIsOpen = ref<boolean>(false);

const editBranchContactPayload = ref<EditBranchContactPayload | null>(null);

const handleAdd = (regional?: Regional) => {
    formBranchContactIsOpen.value = true;
    editBranchContactPayload.value = {
        target_type: 'BM',
    };

    if (regional) {
        selectedRegional.value = regional;
    }
};

const handleEditBranchContact = (contact: BranchContact, regional?: Regional) => {
    if (regional) {
        selectedRegional.value = regional;
    }
    editBranchContactPayload.value = {
        target_type: 'BM',
        branch_contact: contact,
    };
    formBranchContactIsOpen.value = true;
};

const handleEditDbmscContact = (
    contact: DbmscContact | null,
    branch_contact: BranchContact,
) => {
    editBranchContactPayload.value = {
        target_type: 'DBMSC',
        dbmsc_contact: contact,
    };
    selectedBranchContact.value = branch_contact;
    formBranchContactIsOpen.value = true;
};

watch(
    () => props.contacts,
    (newContacts) => {
        if (!selectedRegional.value) return;

        const updatedRegional = newContacts.find(
            (r) => r.id === selectedRegional.value?.id,
        );
        if (updatedRegional) {
            selectedRegional.value = updatedRegional;
        }
    },
    { deep: true },
);

const clickRegional = (regional: Regional) => {
    selectedRegional.value = regional;
    selectedBranchContact.value = null;
};

const searchQuery = ref(
    new URLSearchParams(window.location.search).get('search') || '',
);

debouncedWatch(
    searchQuery,
    (newQuery) => {
        const query = {
            search: newQuery,
        };

        selectedRegional.value = null;

        router.get(window.location.pathname, query, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    },
    {
        debounce: 200,
    },
);
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
                    class="flex min-w-0 flex-1 flex-col gap-4 rounded-xl border p-4 shadow-sm sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <h2
                                class="text-base font-bold text-slate-900 dark:text-slate-100"
                            >
                                <template v-if="!searchQuery && !selectedRegional">
                                    Pilih Regional
                                </template>
                                    <template v-else-if="selectedRegional">
                                        {{ selectedRegional.name }}
                                    </template>
                                <template v-else> Cari kontak </template>
                            </h2>
                            <span
                                v-if="!searchQuery"
                                class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                            >
                                {{ showedContacts.length }} kontak
                            </span>
                        </div>
                        <div
                            class="flex flex-col gap-2 min-[480px]:flex-row min-[480px]:items-center"
                        >
                            <div
                                class="relative w-full min-[480px]:w-48 lg:w-64"
                            >
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Cari kontak..."
                                    class="h-9 w-full pr-8"
                                />
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400"
                                >
                                    <Search class="h-[14px] w-[14px]" />
                                </div>
                            </div>
                            <Button
                                v-if="selectedRegional"
                                size="sm"
                                @click="handleAdd"
                                class="cursor-pointer gap-1.5 bg-blue-600 whitespace-nowrap hover:bg-blue-700"
                            >
                                <PlusIcon class="h-4 w-4" />
                                <span class="hidden min-[400px]:inline">
                                    Tambah Kontak BM
                                </span>
                                <span class="inline min-[400px]:hidden">
                                    Tambah BM
                                </span>
                            </Button>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="showedContacts.length === 0 && selectedRegional"
                        class="flex flex-1 flex-col items-center justify-center gap-3 py-16"
                    >
                        <UsersIcon class="h-10 w-10 text-slate-300" />
                        <p class="text-sm font-medium text-slate-700">
                            Belum ada kontak
                        </p>
                        <p class="text-center text-xs text-slate-500">
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
                        v-if="!selectedRegional && !searchQuery"
                        class="flex flex-1 flex-col items-center justify-center gap-3 py-16"
                    >
                        <MapPinIcon class="h-10 w-10 text-slate-300" />
                        <p class="text-sm font-medium">Pilih Regional</p>
                        <p class="text-center text-xs text-slate-500">
                            Silakan pilih salah satu regional untuk melihat
                            kontak BM yang terdaftar.
                        </p>
                    </div>

                    <div
                        v-if="searchQuery && !selectedRegional"
                        class="flex flex-col gap-4"
                    >
                        <div
                            v-for="regional in contacts"
                            :key="`s-${regional.id}`"
                        >
                            <h3
                                class="mb-2 flex items-center gap-2 text-sm font-bold text-blue-600"
                            >
                                <MapPinIcon class="h-4 w-4" />
                                Regional {{ regional.name }}
                            </h3>

                            <div
                                class="grid items-start gap-6 p-1 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-3"
                            >
                                <div
                                    class="group/branch flex flex-col items-center rounded-2xl border border-slate-100 bg-slate-50/40 p-5 shadow-sm transition-all hover:bg-slate-50/80 hover:shadow-md"
                                    v-for="contact in regional.branch_contacts"
                                    :key="contact.id"
                                >
                                    <template v-if="contact">
                                        <BranchContactCard
                                            mode="view"
                                            :contact="contact"
                                            @add-dbmsc-contact="handleEditDbmscContact(null,  contact)"
                                            @edit="handleEditBranchContact(contact, regional)"
                                            class="w-full"
                                        />

                                        <template v-if="contact.dbmsc_contact">
                                            <div
                                                class="relative flex h-10 w-full items-center justify-center overflow-hidden"
                                            >
                                                <div class="mb-1 h-full w-0.5 bg-slate-400"></div>
                                                <div class="absolute bottom-[2px] h-2 w-2 rotate-45 border-r-2 border-b-2 border-slate-400"></div>
                                            </div>

                                            <DbmscContactCard
                                                :branch_contact="contact"
                                                :contact="contact.dbmsc_contact"
                                                @edit="handleEditDbmscContact"
                                                class="w-full"
                                            />
                                        </template>
                                        <div v-else class="h-10"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid kontak dengan tampilan bagan -->
                    <div
                        v-else
                        class="grid items-start gap-6 p-1 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <div
                            class="group/branch flex flex-col items-center rounded-2xl border border-slate-100 bg-slate-50/40 p-5 shadow-sm transition-all hover:bg-slate-50/80 hover:shadow-md"
                            v-for="contact in showedContacts"
                            :key="contact.id"
                        >
                            <BranchContactCard
                                @add-dbmsc-contact="
                                    handleEditDbmscContact(null, contact)
                                "
                                @edit="handleEditBranchContact"
                                :contact="contact"
                                class="w-full"
                            />

                            <template v-if="contact.dbmsc_contact">
                                <div
                                    class="relative flex h-10 w-full items-center justify-center overflow-hidden"
                                >
                                    <div
                                        class="mb-1 h-full w-0.5 bg-slate-400"
                                    ></div>
                                    <div
                                        class="absolute bottom-[2px] h-2 w-2 rotate-45 border-r-2 border-b-2 border-slate-400"
                                    ></div>
                                </div>

                                <DbmscContactCard
                                    :branch_contact="contact"
                                    :contact="contact.dbmsc_contact"
                                    @edit="handleEditDbmscContact"
                                    class="w-full"
                                />
                            </template>

                            <div v-else class="h-10"></div>
                        </div>
                    </div>
                </div>
            </div>

            <FormBranchContactModal
                v-model:form-contact-is-open="formBranchContactIsOpen"
                v-model:selected-branch-contact="selectedBranchContact"
                v-model:selected-regional="selectedRegional"
                v-model:edit-payload="editBranchContactPayload"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
