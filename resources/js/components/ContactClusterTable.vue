<script setup lang="ts">
import {
    Area,
    Branch,
    ContactCluster,
    EditContactPayload,
    Regional,
    StcTlContact,
} from '@/types';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { TooltipProvider } from '@/components/ui/tooltip';
import { Building2, MapPin, Landmark } from 'lucide-vue-next';
import { ref } from 'vue';
import ContactNameCell from '@/components/ContactNameCell.vue';
import NipCell from '@/components/NipCell.vue';
import EditCell from '@/components/EditCell.vue';
import PhoneCell from '@/components/PhoneCell.vue';
import { createWhatsappLink } from '@/lib/utils';
import BucketCell from '@/components/BucketCell.vue';

defineProps<{
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

const selectedRegion = defineModel<{
    regional: Regional | null;
    area: Area | null;
    branch: Branch | null;
} | null>('selected-region', {
    default: null,
});

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

const openedBranches = ref(new Set());

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

const handleClickBranch = (id: number) => {
    if (openedBranches.value.has(id)) {
        openedBranches.value.delete(id);
    } else {
        openedBranches.value.add(id);
    }
    console.log(openedBranches.value);
};

const handleClickRegion = (
    regional: Regional | null = null,
    area: Area | null = null,
    branch: Branch | null = null,
) => {
    selectedRegion.value = {
        regional: regional,
        area: area,
        branch: branch,
    };
};

function onEnter(el: Element) {
    const wrapper = (el as HTMLElement).querySelector(
        '.accordion-wrapper',
    ) as HTMLElement;
    wrapper.style.height = '0';
    wrapper.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        wrapper.style.transition = 'height 0.3s ease';
        wrapper.style.height = wrapper.scrollHeight + 'px';
    });
}

function onAfterEnter(el: Element) {
    const wrapper = (el as HTMLElement).querySelector(
        '.accordion-wrapper',
    ) as HTMLElement;
    wrapper.style.height = 'auto';
    wrapper.style.overflow = '';
    wrapper.style.transition = '';
}

function onLeave(el: Element) {
    const wrapper = (el as HTMLElement).querySelector(
        '.accordion-wrapper',
    ) as HTMLElement;
    wrapper.style.height = wrapper.scrollHeight + 'px';
    wrapper.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        wrapper.style.transition = 'height 0.3s ease';
        wrapper.style.height = '0';
    });
}
</script>

<template>
    <TooltipProvider :delay-duration="200">
        <div class="overflow-x-auto rounded-xl border border-border shadow-sm">
            <Table class="border-separate border-spacing-0">
                <!-- ── HEADER ─────────────────────────────────────────────── -->
                <TableHeader class="sticky top-0 z-20 bg-background">
                    <TableRow class="border-b-0">
                        <TableHead
                            class="sticky left-0 z-30 border-r border-b bg-background text-center font-semibold tracking-wider uppercase"
                        >
                            Wilayah
                        </TableHead>

                        <!-- RLQH sub-cols -->
                        <TableHead class="border-r border-b text-center"
                            >Nama</TableHead
                        >
                        <TableHead class="border-r border-b text-center"
                            >NIP</TableHead
                        >
                        <TableHead class="border-r border-b text-center"
                            >Kontak</TableHead
                        >
                        <TableHead class="w-10 border-r border-b"></TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-for="regional in regionals" :key="regional.id">
                        <!-- ── REGIONAL ROW ──────────────────────────────── -->
                        <TableRow
                            class="group border-t-2 border-border bg-muted/30 transition-colors"
                        >
                            <!-- Level label -->
                            <TableCell
                                class="sticky left-0 z-10 border-r border-b border-border py-3 transition-colors"
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
                                            @click="
                                                handleClickRegion(
                                                    regional,
                                                    null,
                                                    null,
                                                )
                                            "
                                            class="text-sm leading-tight font-bold text-foreground hover:cursor-pointer hover:underline"
                                        >
                                            {{ regional.name }}
                                        </p>
                                    </div>
                                </div>
                            </TableCell>

                            <!-- RLQH – Regional -->
                            <TableCell
                                class="border-r border-b border-border/40 py-3 text-center transition-colors"
                            >
                                <ContactNameCell
                                    :name="regional.contact_cluster?.name"
                                />
                            </TableCell>
                            <TableCell
                                class="border-r border-b border-border/40 py-3 text-center transition-colors"
                            >
                                <NipCell :nip="regional.contact_cluster?.nip" />
                            </TableCell>
                            <TableCell
                                class="border-r border-b border-border/40 py-3 text-center transition-colors"
                            >
                                <PhoneCell
                                    :phone="regional.contact_cluster?.phone"
                                    :wa-link="
                                        createWhatsappLink(
                                            regional.contact_cluster?.phone ||
                                                null,
                                        )
                                    "
                                    :entity-id="regional.id"
                                    :copied-id="copiedId"
                                    @copy="copyPhone"
                                />
                            </TableCell>
                            <TableCell
                                class="border-r border-b border-border py-3 transition-colors"
                            >
                                <EditCell
                                    @edit="
                                        editRegionalContact(
                                            regional,
                                            regional.contact_cluster,
                                        )
                                    "
                                />
                            </TableCell>
                        </TableRow>

                        <!-- ── AREA ROWS ─────────────────────────────────── -->
                        <template v-for="area in regional.areas" :key="area.id">
                            <TableRow
                                class="group border-t border-border/60 transition-colors"
                            >
                                <!-- Area name -->
                                <TableCell
                                    class="sticky left-0 z-10 border-r border-b border-border/40 py-2.5 transition-colors"
                                >
                                    <div class="flex items-center gap-2 pl-4">
                                        <div
                                            class="h-full w-0.5 shrink-0 self-stretch rounded-full bg-border"
                                        ></div>
                                        <div
                                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600"
                                        >
                                            <MapPin class="h-3 w-3" />
                                        </div>
                                        <div>
                                            <Badge
                                                variant="outline"
                                                class="mb-0.5 border-sky-200 px-1.5 py-0 text-[10px] font-semibold tracking-wider text-sky-700 uppercase"
                                            >
                                                Area
                                            </Badge>
                                            <p
                                                @click="
                                                    handleClickRegion(
                                                        regional,
                                                        area,
                                                        null,
                                                    )
                                                "
                                                class="text-sm font-semibold hover:cursor-pointer hover:underline"
                                            >
                                                {{ area.name }}
                                            </p>
                                        </div>
                                    </div>
                                </TableCell>

                                <!-- RLQH – Area (rowspan) -->
                                <TableCell
                                    class="border-r border-b border-border/40 py-2.5 text-center align-middle transition-colors"
                                >
                                    <ContactNameCell
                                        :name="area.contact_cluster?.name"
                                    />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border/40 py-2.5 text-center align-middle transition-colors"
                                >
                                    <NipCell :nip="area.contact_cluster?.nip" />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border/40 py-2.5 text-center align-middle transition-colors"
                                >
                                    <PhoneCell
                                        :phone="area.contact_cluster?.phone"
                                        :wa-link="
                                            createWhatsappLink(
                                                area.contact_cluster?.phone ||
                                                    null,
                                            )
                                        "
                                        :entity-id="area.id"
                                        :copied-id="copiedId"
                                        @copy="copyPhone"
                                    />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border py-2.5 align-middle transition-colors"
                                >
                                    <EditCell
                                        @edit="
                                            editAreaContact(
                                                area,
                                                area.contact_cluster,
                                            )
                                        "
                                    />
                                </TableCell>
                            </TableRow>

                            <!-- Branch rows inside Area -->
                            <template
                                v-for="branch in area.branches"
                                :key="branch.id"
                            >
                                <TableRow
                                    class="group border-t border-dashed border-border/40 transition-colors"
                                >
                                    <!-- Branch name (Wilayah) -->
                                    <TableCell
                                        class="sticky left-0 z-10 border-r border-b border-border/40 py-2 align-middle transition-colors"
                                    >
                                        <div
                                            class="flex items-center gap-2 pl-10"
                                        >
                                            <div
                                                class="h-full w-0.5 shrink-0 self-stretch rounded-full bg-border/50"
                                            ></div>
                                            <div
                                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-muted text-muted-foreground"
                                            >
                                                <Building2
                                                    class="h-2.5 w-2.5"
                                                />
                                            </div>
                                            <p
                                                @click="
                                                    handleClickBranch(branch.id)
                                                "
                                                class="text-sm text-foreground/90 hover:cursor-pointer hover:underline"
                                            >
                                                {{ branch.name }}
                                            </p>
                                        </div>
                                    </TableCell>

                                    <!-- CLQH Branch (Data) -->
                                    <TableCell
                                        class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                    >
                                        <ContactNameCell
                                            :name="branch.contact_cluster?.name"
                                        />
                                    </TableCell>
                                    <TableCell
                                        class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                    >
                                        <NipCell
                                            :nip="branch.contact_cluster?.nip"
                                        />
                                    </TableCell>
                                    <TableCell
                                        class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                    >
                                        <PhoneCell
                                            :phone="
                                                branch.contact_cluster?.phone
                                            "
                                            :wa-link="
                                                createWhatsappLink(
                                                    branch.contact_cluster
                                                        ?.phone || null,
                                                )
                                            "
                                            :entity-id="branch.id"
                                            :copied-id="copiedId"
                                            @copy="copyPhone"
                                        />
                                    </TableCell>
                                    <TableCell
                                        class="border-r border-b border-border py-2 align-middle transition-colors"
                                    >
                                        <EditCell
                                            @edit="
                                                editBranchContact(
                                                    branch,
                                                    branch.contact_cluster,
                                                )
                                            "
                                        />
                                    </TableCell>
                                </TableRow>
                                <!-- EXPANDABLE ROW -->
                                <Transition
                                    name="accordion"
                                    @enter="onEnter"
                                    @after-enter="onAfterEnter"
                                    @leave="onLeave"
                                >
                                    <TableRow
                                        v-if="openedBranches.has(branch.id)"
                                    >
                                        <TableCell
                                            colspan="5"
                                            class="border-b p-0"
                                        >
                                            <div class="accordion-wrapper">
                                                <div
                                                    class="flex flex-row gap-4 p-4"
                                                >
                                                    <!-- STC SECTION -->
                                                    <div class="w-1/2">
                                                        <div
                                                            class="mb-1.5 flex items-center justify-between"
                                                        >
                                                            <h4
                                                                class="font-semibold"
                                                            >
                                                                STC
                                                                {{
                                                                    branch.name
                                                                }}
                                                            </h4>
                                                            <button
                                                                @click="
                                                                    handleAddStcTlContact(
                                                                        branch,
                                                                        'STC',
                                                                    )
                                                                "
                                                                class="rounded bg-primary px-3 py-1 text-xs text-white"
                                                            >
                                                                + Tambah STC
                                                            </button>
                                                        </div>

                                                        <div
                                                            class="space-y-1.5"
                                                        >
                                                            <div
                                                                v-for="stc in getStcContacts(
                                                                    branch,
                                                                )"
                                                                :key="stc.id"
                                                                class="relative rounded border bg-background p-2"
                                                            >
                                                                <!-- tombol edit -->
                                                                <button
                                                                    @click="
                                                                        handleEditStcTlContact(
                                                                            branch,
                                                                            'STC',
                                                                            stc,
                                                                        )
                                                                    "
                                                                    class="absolute top-2 right-2 rounded bg-muted px-2 py-1 text-xs transition-all group-hover:visible hover:bg-primary/10 hover:text-primary active:scale-95"
                                                                >
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none"
                                                                        viewBox="0 0 24 24"
                                                                        stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        stroke="currentColor"
                                                                        class="h-3.5 w-3.5"
                                                                    >
                                                                        <path
                                                                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"
                                                                        />
                                                                        <path
                                                                            d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                                                        />
                                                                    </svg>
                                                                </button>

                                                                <div
                                                                    class="pr-8 font-medium"
                                                                >
                                                                    {{
                                                                        stc.name
                                                                    }}
                                                                </div>

                                                                <PhoneCell
                                                                    class="block"
                                                                    :phone="
                                                                        stc.phone
                                                                    "
                                                                    :wa-link="
                                                                        createWhatsappLink(
                                                                            stc.phone ||
                                                                                null,
                                                                        )
                                                                    "
                                                                    :entity-id="
                                                                        stc.id
                                                                    "
                                                                    :copied-id="
                                                                        copiedId
                                                                    "
                                                                    @copy="
                                                                        copyPhone
                                                                    "
                                                                />

                                                                <BucketCell
                                                                    :categories="
                                                                        stc.categories
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- TL SECTION -->
                                                    <div class="w-1/2">
                                                        <div
                                                            class="mb-1.5 flex items-center justify-between"
                                                        >
                                                            <h4
                                                                class="font-semibold"
                                                            >
                                                                TL
                                                                {{
                                                                    branch.name
                                                                }}
                                                            </h4>
                                                            <button
                                                                @click="
                                                                    handleAddStcTlContact(
                                                                        branch,
                                                                        'TL',
                                                                    )
                                                                "
                                                                class="rounded bg-primary px-3 py-1 text-xs text-white"
                                                            >
                                                                + Tambah TL
                                                            </button>
                                                        </div>

                                                        <div
                                                            class="space-y-1.5"
                                                        >
                                                            <div
                                                                v-for="tl in getTlContacts(
                                                                    branch,
                                                                )"
                                                                :key="tl.id"
                                                                class="relative rounded border bg-background p-2"
                                                            >
                                                                <button
                                                                    @click="
                                                                        handleEditStcTlContact(
                                                                            branch,
                                                                            'TL',
                                                                            tl,
                                                                        )
                                                                    "
                                                                    class="absolute top-2 right-2 rounded bg-muted px-2 py-1 text-xs transition-all group-hover:visible hover:bg-primary/10 hover:text-primary active:scale-95"
                                                                >
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none"
                                                                        viewBox="0 0 24 24"
                                                                        stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        stroke="currentColor"
                                                                        class="h-3.5 w-3.5"
                                                                    >
                                                                        <path
                                                                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"
                                                                        />
                                                                        <path
                                                                            d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                                                        />
                                                                    </svg>
                                                                </button>
                                                                <div
                                                                    class="pr-8 font-medium"
                                                                >
                                                                    {{
                                                                        tl.name
                                                                    }}
                                                                </div>
                                                                <PhoneCell
                                                                    :phone="
                                                                        tl.phone
                                                                    "
                                                                    :wa-link="
                                                                        createWhatsappLink(
                                                                            tl.phone ||
                                                                                null,
                                                                        )
                                                                    "
                                                                    :entity-id="
                                                                        tl.id
                                                                    "
                                                                    :copied-id="
                                                                        copiedId
                                                                    "
                                                                    @copy="
                                                                        copyPhone
                                                                    "
                                                                />
                                                                <BucketCell
                                                                    :categories="
                                                                        tl.categories
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </Transition>
                            </template>
                        </template>

                        <!-- ── BRANCH DIRECT TO REGIONAL ────────────────── -->
                        <template
                            v-for="branch in regional.branches"
                            :key="branch.id"
                        >
                            <TableRow
                                class="group border-t border-dashed border-border/40 transition-colors"
                            >
                                <TableCell
                                    class="sticky left-0 z-10 border-r border-b border-border/40 py-2 align-middle transition-colors"
                                >
                                    <div class="flex items-center gap-2 pl-4">
                                        <div
                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-muted text-muted-foreground"
                                        >
                                            <Building2 class="h-2.5 w-2.5" />
                                        </div>
                                        <p
                                            @click="
                                                handleClickBranch(branch.id)
                                            "
                                            class="text-sm text-foreground/90 hover:cursor-pointer hover:underline"
                                        >
                                            {{ branch.name }}
                                        </p>
                                    </div>
                                </TableCell>

                                <!-- CLQH Cells -->
                                <TableCell
                                    class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                >
                                    <ContactNameCell
                                        :name="branch.contact_cluster?.name"
                                    />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                >
                                    <NipCell
                                        :nip="branch.contact_cluster?.nip"
                                    />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                >
                                    <PhoneCell
                                        :phone="branch.contact_cluster?.phone"
                                        :wa-link="
                                            createWhatsappLink(
                                                branch.contact_cluster?.phone ||
                                                    '',
                                            )
                                        "
                                        :entity-id="branch.id"
                                        :copied-id="copiedId"
                                        @copy="copyPhone"
                                    />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border py-2 align-middle transition-colors"
                                >
                                    <EditCell
                                        @edit="
                                            editBranchContact(
                                                branch,
                                                branch.contact_cluster,
                                            )
                                        "
                                    />
                                </TableCell>
                            </TableRow>
                            <!-- EXPANDABLE ROW -->
                            <Transition
                                name="accordion"
                                @enter="onEnter"
                                @after-enter="onAfterEnter"
                                @leave="onLeave"
                            >
                                <TableRow v-if="openedBranches.has(branch.id)">
                                    <TableCell colspan="5" class="border-b p-0">
                                        <div class="accordion-wrapper">
                                            <div
                                                class="flex flex-row gap-4 p-4"
                                            >
                                                <!-- STC SECTION -->
                                                <div class="w-1/2">
                                                    <div
                                                        class="mb-1.5 flex items-center justify-between"
                                                    >
                                                        <h4
                                                            class="font-semibold"
                                                        >
                                                            STC
                                                            {{ branch.name }}
                                                        </h4>
                                                        <button
                                                            @click="
                                                                handleAddStcTlContact(
                                                                    branch,
                                                                    'STC',
                                                                )
                                                            "
                                                            class="rounded bg-primary px-3 py-1 text-xs text-white"
                                                        >
                                                            + Tambah STC
                                                        </button>
                                                    </div>

                                                    <div class="space-y-1.5">
                                                        <div
                                                            v-for="stc in getStcContacts(
                                                                branch,
                                                            )"
                                                            :key="stc.id"
                                                            class="relative rounded border bg-background p-2"
                                                        >
                                                            <!-- tombol edit -->
                                                            <button
                                                                @click="
                                                                    handleEditStcTlContact(
                                                                        branch,
                                                                        'STC',
                                                                        stc,
                                                                    )
                                                                "
                                                                class="absolute top-2 right-2 rounded bg-muted px-2 py-1 text-xs transition-all group-hover:visible hover:bg-primary/10 hover:text-primary active:scale-95"
                                                            >
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="2"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke="currentColor"
                                                                    class="h-3.5 w-3.5"
                                                                >
                                                                    <path
                                                                        d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"
                                                                    />
                                                                    <path
                                                                        d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                                                    />
                                                                </svg>
                                                            </button>

                                                            <div
                                                                class="pr-8 font-medium"
                                                            >
                                                                {{ stc.name }}
                                                            </div>

                                                            <PhoneCell
                                                                class="block"
                                                                :phone="
                                                                    stc.phone
                                                                "
                                                                :wa-link="
                                                                    createWhatsappLink(
                                                                        stc.phone ||
                                                                            null,
                                                                    )
                                                                "
                                                                :entity-id="
                                                                    stc.id
                                                                "
                                                                :copied-id="
                                                                    copiedId
                                                                "
                                                                @copy="
                                                                    copyPhone
                                                                "
                                                            />

                                                            <BucketCell
                                                                :categories="
                                                                    stc.categories
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- TL SECTION -->
                                                <div class="w-1/2">
                                                    <div
                                                        class="mb-1.5 flex items-center justify-between"
                                                    >
                                                        <h4
                                                            class="font-semibold"
                                                        >
                                                            TL {{ branch.name }}
                                                        </h4>
                                                        <button
                                                            @click="
                                                                handleAddStcTlContact(
                                                                    branch,
                                                                    'TL',
                                                                )
                                                            "
                                                            class="rounded bg-primary px-3 py-1 text-xs text-white"
                                                        >
                                                            + Tambah TL
                                                        </button>
                                                    </div>

                                                    <div class="space-y-1.5">
                                                        <div
                                                            v-for="tl in getTlContacts(
                                                                branch,
                                                            )"
                                                            :key="tl.id"
                                                            class="relative rounded border bg-background p-2"
                                                        >
                                                            <button
                                                                @click="
                                                                    handleEditStcTlContact(
                                                                        branch,
                                                                        'TL',
                                                                        tl,
                                                                    )
                                                                "
                                                                class="absolute top-2 right-2 rounded bg-muted px-2 py-1 text-xs transition-all group-hover:visible hover:bg-primary/10 hover:text-primary active:scale-95"
                                                            >
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="2"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke="currentColor"
                                                                    class="h-3.5 w-3.5"
                                                                >
                                                                    <path
                                                                        d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"
                                                                    />
                                                                    <path
                                                                        d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                                                    />
                                                                </svg>
                                                            </button>
                                                            <div
                                                                class="pr-8 font-medium text-sm"
                                                            >
                                                                {{ tl.name }}
                                                            </div>
                                                            <PhoneCell
                                                                :phone="
                                                                    tl.phone
                                                                "
                                                                :wa-link="
                                                                    createWhatsappLink(
                                                                        tl.phone ||
                                                                            null,
                                                                    )
                                                                "
                                                                :entity-id="
                                                                    tl.id
                                                                "
                                                                :copied-id="
                                                                    copiedId
                                                                "
                                                                @copy="
                                                                    copyPhone
                                                                "
                                                            />
                                                            <BucketCell
                                                                :categories="
                                                                    tl.categories
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </Transition>
                        </template>
                    </template>
                </TableBody>
            </Table>
        </div>
    </TooltipProvider>
</template>

<style scoped>
:deep(table) {
    display: block;
    overflow-y: auto;
    max-height: calc(100vh - 6rem);
    min-height: 570px;
}

:deep(thead) {
    display: table-header-group;
    position: sticky;
    top: 0;
    z-index: 20;
    background: var(--background, #ffffff);
}

:deep(tbody) {
    display: table-row-group;
}

:deep(tr) {
    display: table-row;
    width: 100%;
}

:deep(th),
:deep(td) {
    display: table-cell;
}

.accordion-enter-active,
.accordion-leave-active {
    transition: opacity 0.3s ease;
}

.accordion-enter-from,
.accordion-leave-to {
    opacity: 0;
}
</style>
