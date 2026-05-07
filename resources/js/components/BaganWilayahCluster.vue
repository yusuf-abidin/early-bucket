<script setup lang="ts">
import {
    Area,
    Branch,
    ContactCluster,
    EditContactPayload,
    Regional,
    StcTlContact,
} from '@/types';
import { nextTick, ref, watch } from 'vue';
import Bagan from '@/components/Bagan.vue';
import { ChevronDown, UsersRound, Pencil } from 'lucide-vue-next';
import { Landmark } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import PhoneCell from '@/components/PhoneCell.vue';
import { createWhatsappLink } from '@/lib/utils';

const props = defineProps<{
    regionals: Regional[];
}>();

const formContactIsOpen = defineModel<boolean>('formContactIsOpen', {
    default: false,
});
const editContactPayload = defineModel<EditContactPayload | null>(
    'editContactPayload',
    {
        default: null,
    },
);

const formStcTlIsOpen = defineModel<boolean>('form-stc-tl-is-open', {
    default: false,
});

const editStcTlContact = defineModel<{
    branch: Branch;
    role: 'STC' | 'TL';
    contact: StcTlContact | null;
} | null>('edit-stc-tl-contact', {
    default: null,
});

const openedRegional = ref(new Set());
const selectedBranch = ref<Branch | null>(null);

const handleSelectBranch = async (branch: Branch | null) => {
    selectedBranch.value = branch;
    if (branch) {
        await nextTick();
        const container = document.getElementById(`stc-tl-detail-${branch.id}`);
        if (container) {
            const margin = 10;
            const elementBottom =
                container.getBoundingClientRect().bottom + window.scrollY;
            const targetY = elementBottom - window.innerHeight + margin;
            window.scrollTo({
                top: targetY,
                behavior: 'smooth',
            });
        }
    }
};

const watchRegionals = () => {
    if (!selectedBranch.value) return;

    let updatedBranch: Branch | undefined = undefined;

    for (const regional of props.regionals) {
        for (const area of regional.areas) {
            updatedBranch = area.branches.find(
                (b) => b.id === selectedBranch.value?.id,
            );
            if (updatedBranch) break;
        }
        if (updatedBranch) break;

        updatedBranch = regional.branches?.find(
            (b) => b.id === selectedBranch.value?.id,
        );
        if (updatedBranch) break;
    }
    if (updatedBranch) {
        selectedBranch.value = updatedBranch;
    }
};

watch(
    () => props.regionals,
    () => {
        watchRegionals();
    },
    {
        deep: true,
    },
);

const handleAddStcTlContact = (branch: Branch, role: 'STC' | 'TL') => {
    editStcTlContact.value = {
        branch: branch,
        role: role,
        contact: null,
    };

    formStcTlIsOpen.value = true;
};

const handleEditStcTlContact = (
    branch: Branch,
    role: 'STC' | 'TL',
    contact: StcTlContact,
) => {
    editStcTlContact.value = {
        branch: branch,
        role: role,
        contact: contact,
    };
    formStcTlIsOpen.value = true;
};

const editRegionalContact = (
    regional: Regional | undefined = undefined,
    contact: ContactCluster | undefined = undefined,
) => {
    editContactPayload.value = {
        targetType: 'REGIONAL',
        regional: regional,
        area: undefined,
        branch: undefined,
        contact: contact,
    };
    formContactIsOpen.value = true;
};

const editAreaContact = (
    area: Area | undefined = undefined,
    contact: ContactCluster | undefined = undefined,
) => {
    editContactPayload.value = {
        targetType: 'AREA',
        regional: undefined,
        area: area,
        branch: undefined,
        contact: contact,
    };
    formContactIsOpen.value = true;
};

const editBranchContact = (
    branch: Branch | undefined = undefined,
    contact: ContactCluster | undefined = undefined,
) => {
    editContactPayload.value = {
        targetType: 'BRANCH',
        regional: undefined,
        branch: branch,
        area: undefined,
        contact: contact,
    };
    formContactIsOpen.value = true;
};

const copiedId = ref<number | undefined>(undefined);

const copyPhone = async (phone: string, id: number) => {
    if (!phone) return;
    await navigator.clipboard.writeText(phone);
    copiedId.value = id;
    setTimeout(() => {
        copiedId.value = undefined;
    }, 1500);
};

const getStcContacts = (branch: Branch) =>
    (branch.stc_tl_contacts ?? []).filter((c) => c.role === 'STC');

const getTlContacts = (branch: Branch) =>
    (branch.stc_tl_contacts ?? []).filter((c) => c.role === 'TL');

const handleClickRegional = async (id: number) => {
    const next = new Set(openedRegional.value);
    const isOpening = !next.has(id);

    if (isOpening) {
        next.add(id);
        selectedBranch.value = null; // Reset selection when switching regions
    } else {
        next.delete(id);
    }

    openedRegional.value = next;

    if (isOpening) {
        await nextTick();
        const el = document.getElementById(`regional-container-${id}`);
        if (el) {
            const yOffset = -10; // Jarak margin dari atas layar (pixel)
            const y = el.getBoundingClientRect().top + window.scrollY + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    }
};

const handleEditContactFromChart = (payload: { type: string; raw: any }) => {
    const { type, raw } = payload;
    const contact = raw.contact_cluster;

    if (type === 'regional') {
        editRegionalContact(raw, contact);
    } else if (type === 'area') {
        editAreaContact(raw, contact);
    } else if (type === 'branch') {
        editBranchContact(raw, contact);
    }
};
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="regional in regionals"
            :key="regional.id"
            :id="`regional-container-${regional.id}`"
        >
            <div
                @click="handleClickRegional(regional.id)"
                class="group flex cursor-pointer items-center justify-between rounded-sm border border-border px-2 py-1.5 transition-colors hover:bg-muted"
            >
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <Landmark class="h-3.5 w-3.5" />
                    </div>
                    <div>
                        <Badge
                            variant="secondary"
                            class="mb-0.5 px-1.5 py-0 text-[10px] font-semibold tracking-wider uppercase"
                        >
                            Regional
                        </Badge>
                        <p
                            class="text-sm leading-tight font-bold text-foreground"
                        >
                            {{ regional.name }}
                        </p>
                    </div>
                </div>
                <ChevronDown
                    class="h-4 w-4 text-muted-foreground transition-transform duration-200"
                    :class="{ 'rotate-180': openedRegional.has(regional.id) }"
                />
            </div>
            <div v-if="openedRegional.has(regional.id)" class="mt-2 space-y-4">
                <Bagan
                    :regional="regional"
                    :active-branch-id="selectedBranch?.id"
                    @edit-contact="handleEditContactFromChart"
                    @select-branch="handleSelectBranch"
                />

                <!-- Detail STC & TL Section -->
                <div
                    :id="`stc-tl-detail-${selectedBranch.id}`"
                    v-if="
                        selectedBranch &&
                        (regional.areas.some((a) =>
                            a.branches.some((b) => b.id === selectedBranch?.id),
                        ) ||
                            regional.branches?.some(
                                (b) => b.id === selectedBranch?.id,
                            ))
                    "
                    class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div
                        class="mb-2 flex items-center justify-between border-b border-slate-100 pb-2"
                    >
                        <div class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-blue-600"></div>
                            <h4 class="text-sm font-bold text-slate-900">
                                Daftar STC & TL -
                                {{ selectedBranch.name }}
                            </h4>
                        </div>
                        <button
                            @click="selectedBranch = null"
                            class="cursor-pointer text-xs font-medium text-slate-600 transition-colors hover:text-slate-800"
                        >
                            Tutup Detail
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Kolom STC -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 text-purple-600">
                                    <UsersRound
                                        class="h-4 w-4"
                                    />
                                    <span class="font-medium">STC</span>
                                </div>
                                <button
                                    class="rounded bg-blue-600 hover:bg-blue-700 px-2 py-1 text-xs text-white cursor-pointer"
                                    @click="
                                        handleAddStcTlContact(
                                            selectedBranch,
                                            'STC',
                                        )
                                    "
                                >
                                    + Tambah
                                </button>
                            </div>

                            <div class="grid gap-2">
                                <div
                                    v-for="contact in getStcContacts(
                                        selectedBranch,
                                    )"
                                    :key="contact.id"
                                    class="flex flex-col gap-0.5 rounded border border-slate-100 bg-slate-50/50 p-1 transition-all hover:border-blue-200 hover:bg-blue-50/30"
                                >
                                    <!-- Baris atas: nama + phone + edit -->
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <h2
                                            class="truncate text-sm font-medium text-slate-800"
                                        >
                                            {{ contact.name }}
                                        </h2>
                                        <div
                                            class="flex shrink-0 items-center gap-1"
                                        >
                                            <PhoneCell
                                                :wa-link="
                                                    createWhatsappLink(
                                                        contact.phone ?? '',
                                                    )
                                                "
                                                :phone="contact.phone"
                                                :entity-id="contact.id"
                                                :copied-id="copiedId"
                                            />
                                            <button
                                                @click="
                                                    handleEditStcTlContact(
                                                        selectedBranch!,
                                                        'STC',
                                                        contact,
                                                    )
                                                "
                                                class="ml-1 rounded-full p-1.5 text-slate-300 transition-all hover:bg-white hover:text-blue-600 hover:shadow-sm cursor-pointer"
                                            >
                                                <Pencil  class="w-3.5 h-3.5"/>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Baris bawah: bucket tags (wrap jika banyak) -->
                                    <div class="flex flex-wrap gap-1">
                                        <template
                                            v-if="contact.categories.length > 0"
                                        >
                                            <span
                                                v-for="bucket in contact.categories"
                                                :key="bucket.id"
                                                class="rounded bg-slate-200 px-1.5 py-0.5 text-[11px] font-medium text-slate-800"
                                            >
                                                {{ bucket.name }}
                                            </span>
                                        </template>
                                        <span
                                            v-else
                                            class="text-[10px] text-slate-400 italic"
                                            >-</span
                                        >
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        getStcContacts(selectedBranch)
                                            .length === 0
                                    "
                                    class="rounded border border-dashed border-slate-200 p-4 text-center"
                                >
                                    <p class="text-xs text-slate-400 italic">
                                        Belum ada data STC
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom TL -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <div
                                    class="flex items-center gap-1 text-green-500"
                                >
                                    <UsersRound class="h-4 w-4" />
                                    <span class="font-medium">TL</span>
                                </div>
                                <button
                                    @click="handleAddStcTlContact(selectedBranch, 'TL')"
                                    class="rounded bg-blue-600 hover:bg-blue-700 px-2 py-1 text-xs text-white cursor-pointer"
                                >
                                    + Tambah
                                </button>
                            </div>
                            <div class="grid gap-2">
                                <div
                                    v-for="contact in getTlContacts(
                                        selectedBranch,
                                    )"
                                    :key="contact.id"
                                    class="flex flex-col gap-0.5 rounded border border-slate-100 bg-slate-50/50 p-1 transition-all hover:border-teal-200 hover:bg-teal-50/30"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <h2
                                            class="truncate text-sm font-medium text-slate-800"
                                        >
                                            {{ contact.name }}
                                        </h2>
                                        <div class="flex shrink-0 items-center gap-1">
                                            <PhoneCell :wa-link="createWhatsappLink(contact.phone ?? '')" :phone="contact.phone" :entity-id="contact.id" :copied-id="copiedId" />
                                            <button
                                                 class="ml-1 rounded-full p-1.5 text-slate-300 transition-all hover:bg-white hover:text-blue-600 hover:shadow-sm cursor-pointer"
                                                @click="handleEditStcTlContact(selectedBranch!, 'TL', contact)"
                                            >
                                                <Pencil  class="w-3.5 h-3.5"/>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-1">
                                        <template v-if="contact.categories.length > 0">
                                            <span
                                                v-for="bucket in contact.categories"
                                                :key="bucket.id"
                                                class="rounded bg-slate-200 px-1.5 py-0.5 text-[11px] font-medium text-slate-800"
                                            >
                                                {{ bucket.name }}
                                            </span>
                                        </template>
                                        <span v-else class="text-[10px] text-slate-400 italic">-</span>
                                    </div>
                                </div>
                                <div
                                    v-if="
                                        getTlContacts(selectedBranch).length ===
                                        0
                                    "
                                    class="rounded border border-dashed border-slate-200 p-4 text-center"
                                >
                                    <p class="text-xs text-slate-400 italic">
                                        Belum ada data TL
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
