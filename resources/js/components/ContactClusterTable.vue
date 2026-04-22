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
import { Button } from '@/components/ui/button';
import { TooltipProvider } from '@/components/ui/tooltip';
import { Building2, MapPin, Landmark, UserPlus } from 'lucide-vue-next';
import { ref } from 'vue';
import ContactNameCell from '@/components/ContactNameCell.vue';
import NipCell from '@/components/NipCell.vue';
import EditCell from '@/components/EditCell.vue';
import PhoneCell from '@/components/PhoneCell.vue';

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

const handleAddStcTlContact = (branch: Branch, role: 'STC' | 'TL') => {
    editStcTlContact.value = {
        branch: branch,
        role: role,
        contact: null,
    }

    formStcTlIsOpen.value = true;
}

const handleEditStcTlContact = (branch: Branch, role: 'STC' | 'TL', contact: StcTlContact) => {
    editStcTlContact.value = {
        branch: branch,
        role: role,
        contact: contact,
    }
    formStcTlIsOpen.value = true;
}
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

const createWhatsappLink = (
    phone: string | null,
    message?: string,
): string | undefined => {
    if (!phone) return undefined;
    let cleaned = phone.replace(/\D/g, '');

    const isValid = /^(?:62|0)?8\d{7,12}$/.test(cleaned);
    if (!isValid) return undefined;

    if (cleaned.startsWith('62')) {
        // sudah benar
    } else if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.slice(1);
    } else if (cleaned.startsWith('8')) {
        cleaned = '62' + cleaned;
    }
    const baseUrl = `https://wa.me/${cleaned}`;
    if (!message) return baseUrl;
    return `${baseUrl}?text=${encodeURIComponent(message)}`;
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

const activeHover = ref<{
    id: number | null;
    type: 'regional' | 'area' | 'branch' | null;
    section: 'wilayah' | 'rlqh' | 'clqh' | 'stc_tl' | null;
}>({ id: null, type: null, section: null });

const setHover = (
    id: number | null,
    type: 'regional' | 'area' | 'branch' | null = null,
    section: 'wilayah' | 'rlqh' | 'clqh' | 'stc_tl' | null = null,
) => {
    activeHover.value = { id, type, section };
};

const isWilayahHighlighted = (
    id: number,
    type: 'regional' | 'area' | 'branch',
    parentId?: number,
) => {
    const h = activeHover.value;
    if (type === 'regional')
        return h.type === 'regional' && h.id === id && h.section === 'rlqh';
    if (type === 'area')
        return h.type === 'area' && h.id === id && h.section === 'rlqh';
    if (type === 'branch') {
        return (
            (h.type === 'branch' &&
                h.id === id &&
                (h.section === 'clqh' || h.section === 'stc_tl')) ||
            (h.type === 'area' && h.id === parentId && h.section === 'rlqh')
        );
    }
    return false;
};

const isDataHighlighted = (
    id: number,
    type: 'regional' | 'area' | 'branch',
) => {
    const h = activeHover.value;
    return h.id === id && h.type === type && h.section === 'wilayah';
};

const getStcContacts = (branch: Branch) =>
    (branch.stc_tl_contacts ?? []).filter((c) => c.role === 'STC');

const getTlContacts = (branch: Branch) =>
    (branch.stc_tl_contacts ?? []).filter((c) => c.role === 'TL');

const branchRowspan = (branch: Branch) =>
    Math.max(1, getStcContacts(branch).length, getTlContacts(branch).length);

const areaRowspan = (area: Area) =>
    1 + area.branches.reduce((acc, b) => acc + branchRowspan(b), 0);
</script>

<template>
    <TooltipProvider :delay-duration="200">
        <div class="overflow-x-auto rounded-xl border border-border shadow-sm">
            <Table class="border-separate border-spacing-0">
                <!-- ── HEADER ─────────────────────────────────────────────── -->
                <TableHeader>
                    <!-- Row 1 – group labels -->
                    <TableRow class="border-b-0">
                        <TableHead
                            rowspan="2"
                            class="sticky left-0 z-30 border-r border-b bg-background text-center font-semibold tracking-wider uppercase"
                        >
                            Wilayah
                        </TableHead>

                        <!-- RLQH / ALQH group -->
                        <TableHead
                            colspan="4"
                            class="border-r border-b border-border bg-emerald-50/80 text-center"
                        >
                            <div
                                class="flex items-center justify-center gap-1.5"
                            >
                                <span
                                    class="inline-block h-2 w-2 rounded-full bg-emerald-500"
                                ></span>
                                <span
                                    class="font-bold tracking-widest uppercase"
                                >
                                    RLQH / ALQH
                                </span>
                            </div>
                        </TableHead>

                        <!-- CLQH group -->
                        <TableHead
                            colspan="4"
                            class="border-r border-b border-border bg-sky-50/80 text-center"
                        >
                            <div
                                class="flex items-center justify-center gap-1.5"
                            >
                                <span
                                    class="inline-block h-2 w-2 rounded-full bg-sky-500"
                                ></span>
                                <span
                                    class="font-bold tracking-widest uppercase"
                                >
                                    CLQH
                                </span>
                            </div>
                        </TableHead>

                        <!-- STC group -->
                        <TableHead
                            colspan="4"
                            class="border-r border-b border-border bg-violet-50/80 text-center"
                        >
                            <div
                                class="flex items-center justify-center gap-1.5"
                            >
                                <span
                                    class="inline-block h-2 w-2 rounded-full bg-violet-500"
                                ></span>
                                <span
                                    class="font-bold tracking-widest uppercase"
                                    >STC</span
                                >
                            </div>
                        </TableHead>

                        <!-- TL group -->
                        <TableHead
                            colspan="4"
                            class="border-b border-border bg-amber-50/80 text-center"
                        >
                            <div
                                class="flex items-center justify-center gap-1.5"
                            >
                                <span
                                    class="inline-block h-2 w-2 rounded-full bg-amber-500"
                                ></span>
                                <span
                                    class="font-bold tracking-widest uppercase"
                                    >TL</span
                                >
                            </div>
                        </TableHead>
                    </TableRow>

                    <!-- Row 2 – column sub-labels -->
                    <TableRow class="bg-muted/20">
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
                        <!-- CLQH sub-cols -->
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
                        <!-- STC sub-cols -->
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
                        <!-- TL sub-cols -->
                        <TableHead class="border-r border-b text-center"
                            >Nama</TableHead
                        >
                        <TableHead class="border-r border-b text-center"
                            >NIP</TableHead
                        >
                        <TableHead class="border-r border-b text-center"
                            >Kontak</TableHead
                        >
                        <TableHead class="w-10 border-b"></TableHead>
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
                                :class="[
                                    isWilayahHighlighted(
                                        regional.id,
                                        'regional',
                                    )
                                        ? 'bg-muted'
                                        : 'bg-background',
                                ]"
                                @mouseenter="
                                    setHover(regional.id, 'regional', 'wilayah')
                                "
                                @mouseleave="setHover(null)"
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
                            </TableCell>

                            <!-- RLQH – Regional -->
                            <TableCell
                                class="border-r border-b border-border/40 py-3 text-center transition-colors"
                                :class="{
                                    'bg-muted': isDataHighlighted(
                                        regional.id,
                                        'regional',
                                    ),
                                }"
                                @mouseenter="
                                    setHover(regional.id, 'regional', 'rlqh')
                                "
                                @mouseleave="setHover(null)"
                            >
                                <ContactNameCell
                                    :name="regional.contact_cluster?.name"
                                />
                            </TableCell>
                            <TableCell
                                class="border-r border-b border-border/40 py-3 text-center transition-colors"
                                :class="{
                                    'bg-muted': isDataHighlighted(
                                        regional.id,
                                        'regional',
                                    ),
                                }"
                                @mouseenter="
                                    setHover(regional.id, 'regional', 'rlqh')
                                "
                                @mouseleave="setHover(null)"
                            >
                                <NipCell :nip="regional.contact_cluster?.nip" />
                            </TableCell>
                            <TableCell
                                class="border-r border-b border-border/40 py-3 text-center transition-colors"
                                :class="{
                                    'bg-muted': isDataHighlighted(
                                        regional.id,
                                        'regional',
                                    ),
                                }"
                                @mouseenter="
                                    setHover(regional.id, 'regional', 'rlqh')
                                "
                                @mouseleave="setHover(null)"
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
                                :class="{
                                    'bg-muted': isDataHighlighted(
                                        regional.id,
                                        'regional',
                                    ),
                                }"
                                @mouseenter="
                                    setHover(regional.id, 'regional', 'rlqh')
                                "
                                @mouseleave="setHover(null)"
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

                            <!-- Empty cells -->
                            <TableCell class="border-b" /><TableCell
                                class="border-b"
                            /><TableCell class="border-b" /><TableCell
                                class="border-r border-b border-border"
                            />
                            <TableCell class="border-b" /><TableCell
                                class="border-b"
                            /><TableCell class="border-b" /><TableCell
                                class="border-r border-b border-border"
                            />
                            <TableCell class="border-b" /><TableCell
                                class="border-b"
                            /><TableCell class="border-b" /><TableCell
                                class="border-b"
                            />
                        </TableRow>

                        <!-- ── AREA ROWS ─────────────────────────────────── -->
                        <template v-for="area in regional.areas" :key="area.id">
                            <TableRow
                                class="group border-t border-border/60 transition-colors"
                            >
                                <!-- Area name -->
                                <TableCell
                                    class="sticky left-0 z-10 border-r border-b border-border/40 py-2.5 transition-colors"
                                    :class="[
                                        isWilayahHighlighted(area.id, 'area')
                                            ? 'bg-muted'
                                            : 'bg-background',
                                    ]"
                                    @mouseenter="
                                        setHover(area.id, 'area', 'wilayah')
                                    "
                                    @mouseleave="setHover(null)"
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
                                            <p class="text-sm font-semibold">
                                                {{ area.name }}
                                            </p>
                                        </div>
                                    </div>
                                </TableCell>

                                <!-- RLQH – Area (rowspan) -->
                                <TableCell
                                    class="border-r border-b border-border/40 py-2.5 text-center align-middle transition-colors"
                                    :rowspan="areaRowspan(area)"
                                    :class="{
                                        'bg-muted': isDataHighlighted(
                                            area.id,
                                            'area',
                                        ),
                                    }"
                                    @mouseenter="
                                        setHover(area.id, 'area', 'rlqh')
                                    "
                                    @mouseleave="setHover(null)"
                                >
                                    <ContactNameCell
                                        :name="area.contact_cluster?.name"
                                    />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border/40 py-2.5 text-center align-middle transition-colors"
                                    :rowspan="areaRowspan(area)"
                                    :class="{
                                        'bg-muted': isDataHighlighted(
                                            area.id,
                                            'area',
                                        ),
                                    }"
                                    @mouseenter="
                                        setHover(area.id, 'area', 'rlqh')
                                    "
                                    @mouseleave="setHover(null)"
                                >
                                    <NipCell :nip="area.contact_cluster?.nip" />
                                </TableCell>
                                <TableCell
                                    class="border-r border-b border-border/40 py-2.5 text-center align-middle transition-colors"
                                    :rowspan="areaRowspan(area)"
                                    :class="{
                                        'bg-muted': isDataHighlighted(
                                            area.id,
                                            'area',
                                        ),
                                    }"
                                    @mouseenter="
                                        setHover(area.id, 'area', 'rlqh')
                                    "
                                    @mouseleave="setHover(null)"
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
                                    :rowspan="areaRowspan(area)"
                                    :class="{
                                        'bg-muted': isDataHighlighted(
                                            area.id,
                                            'area',
                                        ),
                                    }"
                                    @mouseenter="
                                        setHover(area.id, 'area', 'rlqh')
                                    "
                                    @mouseleave="setHover(null)"
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

                                <!-- Empty cells -->
                                <TableCell class="border-b" /><TableCell
                                    class="border-b"
                                /><TableCell class="border-b" /><TableCell
                                    class="border-r border-b border-border"
                                />
                                <TableCell class="border-b" /><TableCell
                                    class="border-b"
                                /><TableCell class="border-b" /><TableCell
                                    class="border-r border-b border-border"
                                />
                                <TableCell class="border-b" /><TableCell
                                    class="border-b"
                                /><TableCell class="border-b" /><TableCell
                                    class="border-b"
                                />
                            </TableRow>

                            <!-- Branch rows inside Area -->
                            <template
                                v-for="branch in area.branches"
                                :key="branch.id"
                            >
                                <template
                                    v-for="(_, rowIdx) in Array(
                                        branchRowspan(branch),
                                    ).fill(null)"
                                    :key="rowIdx"
                                >
                                    <TableRow
                                        class="group border-t border-dashed border-border/40 transition-colors"
                                    >
                                        <!-- Branch name (Wilayah) -->
                                        <TableCell
                                            v-if="rowIdx === 0"
                                            class="sticky left-0 z-10 border-r border-b border-border/40 py-2 align-middle transition-colors"
                                            :rowspan="branchRowspan(branch)"
                                            :class="[
                                                isWilayahHighlighted(
                                                    branch.id,
                                                    'branch',
                                                    area.id,
                                                )
                                                    ? 'bg-muted'
                                                    : 'bg-background',
                                            ]"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'wilayah',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
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
                                                    class="text-sm text-foreground/90"
                                                >
                                                    {{ branch.name }}
                                                </p>
                                            </div>
                                        </TableCell>

                                        <!-- CLQH Branch (Data) -->
                                        <TableCell
                                            v-if="rowIdx === 0"
                                            class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                            :rowspan="branchRowspan(branch)"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'clqh',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <ContactNameCell
                                                :name="
                                                    branch.contact_cluster?.name
                                                "
                                            />
                                        </TableCell>
                                        <TableCell
                                            v-if="rowIdx === 0"
                                            class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                            :rowspan="branchRowspan(branch)"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'clqh',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <NipCell
                                                :nip="
                                                    branch.contact_cluster?.nip
                                                "
                                            />
                                        </TableCell>
                                        <TableCell
                                            v-if="rowIdx === 0"
                                            class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                            :rowspan="branchRowspan(branch)"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'clqh',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <PhoneCell
                                                :phone="
                                                    branch.contact_cluster
                                                        ?.phone
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
                                            v-if="rowIdx === 0"
                                            class="border-r border-b border-border py-2 align-middle transition-colors"
                                            :rowspan="branchRowspan(branch)"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'clqh',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
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

                                        <!-- STC Contacts (Data) -->
                                        <template
                                            v-if="
                                                getStcContacts(branch)[rowIdx]
                                            "
                                        >
                                            <TableCell
                                                class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <ContactNameCell
                                                    :name="
                                                        getStcContacts(branch)[
                                                            rowIdx
                                                        ].name
                                                    "
                                                />
                                            </TableCell>
                                            <TableCell
                                                class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <NipCell
                                                    :nip="
                                                        getStcContacts(branch)[
                                                            rowIdx
                                                        ].nip
                                                    "
                                                />
                                            </TableCell>
                                            <TableCell
                                                class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <PhoneCell
                                                    :phone="
                                                        getStcContacts(branch)[
                                                            rowIdx
                                                        ].phone
                                                    "
                                                    :wa-link="
                                                        createWhatsappLink(
                                                            getStcContacts(
                                                                branch,
                                                            )[rowIdx].phone,
                                                        )
                                                    "
                                                    :entity-id="
                                                        getStcContacts(branch)[
                                                            rowIdx
                                                        ].id
                                                    "
                                                    :copied-id="copiedId"
                                                    @copy="copyPhone"
                                                />
                                            </TableCell>
                                            <TableCell
                                                class="border-r border-b border-border py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <div
                                                    class="flex items-center justify-center gap-1"
                                                >
                                                    <EditCell
                                                        @edit="handleEditStcTlContact(branch, 'STC', getStcContacts(branch)[rowIdx])"
                                                    />
                                                    <Button
                                                        v-if="
                                                            rowIdx ===
                                                            getStcContacts(
                                                                branch,
                                                            ).length -
                                                                1
                                                        "
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-7 w-7 text-violet-500 hover:bg-violet-50"
                                                        @click="handleAddStcTlContact(branch, 'STC')"
                                                    >
                                                        <UserPlus
                                                            class="h-3.5 w-3.5"
                                                        />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </template>
                                        <template v-else>
                                            <TableCell
                                                v-for="i in 3"
                                                :key="i"
                                                class="border-r border-b border-border/40 transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            />
                                            <TableCell
                                                class="border-r border-b border-border py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <Button
                                                    v-if="
                                                        rowIdx === 0 &&
                                                        getStcContacts(branch)
                                                            .length === 0
                                                    "
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-7 w-7 text-violet-400 hover:bg-violet-50"
                                                    @click="handleAddStcTlContact(branch, 'STC')"
                                                >
                                                    <UserPlus
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </TableCell>
                                        </template>

                                        <!-- TL Contacts (Data) -->
                                        <template
                                            v-if="getTlContacts(branch)[rowIdx]"
                                        >
                                            <TableCell
                                                class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <ContactNameCell
                                                    :name="
                                                        getTlContacts(branch)[
                                                            rowIdx
                                                        ].name
                                                    "
                                                />
                                            </TableCell>
                                            <TableCell
                                                class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <NipCell
                                                    :nip="
                                                        getTlContacts(branch)[
                                                            rowIdx
                                                        ].nip
                                                    "
                                                />
                                            </TableCell>
                                            <TableCell
                                                class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <PhoneCell
                                                    :phone="
                                                        getTlContacts(branch)[
                                                            rowIdx
                                                        ].phone
                                                    "
                                                    :wa-link="
                                                        createWhatsappLink(
                                                            getTlContacts(
                                                                branch,
                                                            )[rowIdx].phone,
                                                        )
                                                    "
                                                    :entity-id="
                                                        getTlContacts(branch)[
                                                            rowIdx
                                                        ].id
                                                    "
                                                    :copied-id="copiedId"
                                                    @copy="copyPhone"
                                                />
                                            </TableCell>
                                            <TableCell
                                                class="border-b py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <div
                                                    class="flex items-center justify-center gap-1"
                                                >
                                                    <EditCell
                                                        @edit="handleEditStcTlContact(branch, 'TL', getTlContacts(branch)[rowIdx])"
                                                    />
                                                    <Button
                                                        v-if="
                                                            rowIdx ===
                                                            getTlContacts(
                                                                branch,
                                                            ).length -
                                                                1
                                                        "
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-7 w-7 text-amber-500 hover:bg-amber-50"
                                                        @click="handleAddStcTlContact(branch, 'TL')"
                                                    >
                                                        <UserPlus
                                                            class="h-3.5 w-3.5"
                                                        />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </template>
                                        <template v-else>
                                            <TableCell
                                                v-for="i in 3"
                                                :key="i"
                                                class="border-r border-b border-border/40 transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            />
                                            <TableCell
                                                class="border-b py-2 text-center transition-colors"
                                                :class="{
                                                    'bg-muted':
                                                        isDataHighlighted(
                                                            branch.id,
                                                            'branch',
                                                        ),
                                                }"
                                                @mouseenter="
                                                    setHover(
                                                        branch.id,
                                                        'branch',
                                                        'stc_tl',
                                                    )
                                                "
                                                @mouseleave="setHover(null)"
                                            >
                                                <Button
                                                    v-if="
                                                        rowIdx === 0 &&
                                                        getTlContacts(branch)
                                                            .length === 0
                                                    "
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-7 w-7 text-amber-400 hover:bg-amber-50"
                                                    @click="handleAddStcTlContact(branch, 'TL')"
                                                >
                                                    <UserPlus
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </TableCell>
                                        </template>
                                    </TableRow>
                                </template>
                            </template>
                        </template>

                        <!-- ── BRANCH DIRECT TO REGIONAL ────────────────── -->
                        <template
                            v-for="branch in regional.branches"
                            :key="branch.id"
                        >
                            <template
                                v-for="(_, rowIdx) in Array(
                                    branchRowspan(branch),
                                ).fill(null)"
                                :key="rowIdx"
                            >
                                <TableRow
                                    class="group border-t border-dashed border-border/40 transition-colors"
                                >
                                    <TableCell
                                        v-if="rowIdx === 0"
                                        class="sticky left-0 z-10 border-r border-b border-border/40 py-2 align-middle transition-colors"
                                        :rowspan="branchRowspan(branch)"
                                        :class="[
                                            isWilayahHighlighted(
                                                branch.id,
                                                'branch',
                                            )
                                                ? 'bg-muted'
                                                : 'bg-background',
                                        ]"
                                        @mouseenter="
                                            setHover(
                                                branch.id,
                                                'branch',
                                                'wilayah',
                                            )
                                        "
                                        @mouseleave="setHover(null)"
                                    >
                                        <div
                                            class="flex items-center gap-2 pl-4"
                                        >
                                            <div
                                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-muted text-muted-foreground"
                                            >
                                                <Building2
                                                    class="h-2.5 w-2.5"
                                                />
                                            </div>
                                            <p
                                                class="text-sm text-foreground/90"
                                            >
                                                {{ branch.name }}
                                            </p>
                                        </div>
                                    </TableCell>

                                    <!-- Empty cells for RLQH -->
                                    <TableCell
                                        v-if="rowIdx === 0"
                                        class="border-b"
                                        :rowspan="branchRowspan(branch)"
                                    /><TableCell
                                        v-if="rowIdx === 0"
                                        class="border-b"
                                        :rowspan="branchRowspan(branch)"
                                    /><TableCell
                                        v-if="rowIdx === 0"
                                        class="border-b"
                                        :rowspan="branchRowspan(branch)"
                                    /><TableCell
                                        v-if="rowIdx === 0"
                                        class="border-r border-b border-border"
                                        :rowspan="branchRowspan(branch)"
                                    />

                                    <!-- CLQH Cells -->
                                    <TableCell
                                        v-if="rowIdx === 0"
                                        class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                        :rowspan="branchRowspan(branch)"
                                        :class="{
                                            'bg-muted': isDataHighlighted(
                                                branch.id,
                                                'branch',
                                            ),
                                        }"
                                        @mouseenter="
                                            setHover(
                                                branch.id,
                                                'branch',
                                                'clqh',
                                            )
                                        "
                                        @mouseleave="setHover(null)"
                                    >
                                        <ContactNameCell
                                            :name="branch.contact_cluster?.name"
                                        />
                                    </TableCell>
                                    <TableCell
                                        v-if="rowIdx === 0"
                                        class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                        :rowspan="branchRowspan(branch)"
                                        :class="{
                                            'bg-muted': isDataHighlighted(
                                                branch.id,
                                                'branch',
                                            ),
                                        }"
                                        @mouseenter="
                                            setHover(
                                                branch.id,
                                                'branch',
                                                'clqh',
                                            )
                                        "
                                        @mouseleave="setHover(null)"
                                    >
                                        <NipCell
                                            :nip="branch.contact_cluster?.nip"
                                        />
                                    </TableCell>
                                    <TableCell
                                        v-if="rowIdx === 0"
                                        class="border-r border-b border-border/40 py-2 text-center align-middle transition-colors"
                                        :rowspan="branchRowspan(branch)"
                                        :class="{
                                            'bg-muted': isDataHighlighted(
                                                branch.id,
                                                'branch',
                                            ),
                                        }"
                                        @mouseenter="
                                            setHover(
                                                branch.id,
                                                'branch',
                                                'clqh',
                                            )
                                        "
                                        @mouseleave="setHover(null)"
                                    >
                                        <PhoneCell
                                            :phone="
                                                branch.contact_cluster?.phone
                                            "
                                            :wa-link="
                                                createWhatsappLink(
                                                    branch.contact_cluster
                                                        ?.phone || '',
                                                )
                                            "
                                            :entity-id="branch.id"
                                            :copied-id="copiedId"
                                            @copy="copyPhone"
                                        />
                                    </TableCell>
                                    <TableCell
                                        v-if="rowIdx === 0"
                                        class="border-r border-b border-border py-2 align-middle transition-colors"
                                        :rowspan="branchRowspan(branch)"
                                        :class="{
                                            'bg-muted': isDataHighlighted(
                                                branch.id,
                                                'branch',
                                            ),
                                        }"
                                        @mouseenter="
                                            setHover(
                                                branch.id,
                                                'branch',
                                                'clqh',
                                            )
                                        "
                                        @mouseleave="setHover(null)"
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

                                    <!-- STC & TL (Data) -->
                                    <template
                                        v-if="getStcContacts(branch)[rowIdx]"
                                    >
                                        <TableCell
                                            class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <ContactNameCell
                                                :name="
                                                    getStcContacts(branch)[
                                                        rowIdx
                                                    ].name
                                                "
                                            />
                                        </TableCell>
                                        <TableCell
                                            class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <NipCell
                                                :nip="
                                                    getStcContacts(branch)[
                                                        rowIdx
                                                    ].nip
                                                "
                                            />
                                        </TableCell>
                                        <TableCell
                                            class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <PhoneCell
                                                :phone="
                                                    getStcContacts(branch)[
                                                        rowIdx
                                                    ].phone
                                                "
                                                :wa-link="
                                                    createWhatsappLink(
                                                        getStcContacts(branch)[
                                                            rowIdx
                                                        ].phone,
                                                    )
                                                "
                                                :entity-id="
                                                    getStcContacts(branch)[
                                                        rowIdx
                                                    ].id
                                                "
                                                :copied-id="copiedId"
                                                @copy="copyPhone"
                                            />
                                        </TableCell>
                                        <TableCell
                                            class="border-r border-b border-border py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <div
                                                class="flex items-center justify-center gap-1"
                                            >
                                                <EditCell
                                                    @edit="handleEditStcTlContact(branch, 'STC', getStcContacts(branch)[rowIdx])"
                                                /><Button
                                                    v-if="
                                                        rowIdx ===
                                                        getStcContacts(branch)
                                                            .length -
                                                            1
                                                    "
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-7 w-7 text-violet-500 hover:bg-violet-50"
                                                    @click="handleAddStcTlContact(branch, 'STC')"
                                                    ><UserPlus
                                                        class="h-3.5 w-3.5"
                                                /></Button>
                                            </div>
                                        </TableCell>
                                    </template>
                                    <template v-else>
                                        <TableCell
                                            v-for="i in 3"
                                            :key="i"
                                            class="border-r border-b border-border/40 transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        /><TableCell
                                            class="border-r border-b border-border py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                            ><Button
                                                v-if="
                                                    rowIdx === 0 &&
                                                    getStcContacts(branch)
                                                        .length === 0
                                                "
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7 text-violet-400 hover:bg-violet-50"
                                                @click="handleAddStcTlContact(branch, 'STC')"
                                                ><UserPlus
                                                    class="h-3.5 w-3.5" /></Button
                                        ></TableCell>
                                    </template>

                                    <!-- TL Contacts (Data) -->
                                    <template
                                        v-if="getTlContacts(branch)[rowIdx]"
                                    >
                                        <TableCell
                                            class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <ContactNameCell
                                                :name="
                                                    getTlContacts(branch)[
                                                        rowIdx
                                                    ].name
                                                "
                                            />
                                        </TableCell>
                                        <TableCell
                                            class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <NipCell
                                                :nip="
                                                    getTlContacts(branch)[
                                                        rowIdx
                                                    ].nip
                                                "
                                            />
                                        </TableCell>
                                        <TableCell
                                            class="border-r border-b border-border/40 py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <PhoneCell
                                                :phone="
                                                    getTlContacts(branch)[
                                                        rowIdx
                                                    ].phone
                                                "
                                                :wa-link="
                                                    createWhatsappLink(
                                                        getTlContacts(branch)[
                                                            rowIdx
                                                        ].phone,
                                                    )
                                                "
                                                :entity-id="
                                                    getTlContacts(branch)[
                                                        rowIdx
                                                    ].id
                                                "
                                                :copied-id="copiedId"
                                                @copy="copyPhone"
                                            />
                                        </TableCell>
                                        <TableCell
                                            class="border-b py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        >
                                            <div
                                                class="flex items-center justify-center gap-1"
                                            >
                                                <EditCell
                                                    @edit="handleEditStcTlContact(branch, 'TL', getTlContacts(branch)[rowIdx])"
                                                /><Button
                                                    v-if="
                                                        rowIdx ===
                                                        getTlContacts(branch)
                                                            .length -
                                                            1
                                                    "
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-7 w-7 text-amber-500 hover:bg-amber-50"
                                                    @click="handleAddStcTlContact(branch, 'TL')"
                                                    ><UserPlus
                                                        class="h-3.5 w-3.5"
                                                /></Button>
                                            </div>
                                        </TableCell>
                                    </template>
                                    <template v-else>
                                        <TableCell
                                            v-for="i in 3"
                                            :key="i"
                                            class="border-r border-b border-border/40 transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                        /><TableCell
                                            class="border-b py-2 text-center transition-colors"
                                            :class="{
                                                'bg-muted': isDataHighlighted(
                                                    branch.id,
                                                    'branch',
                                                ),
                                            }"
                                            @mouseenter="
                                                setHover(
                                                    branch.id,
                                                    'branch',
                                                    'stc_tl',
                                                )
                                            "
                                            @mouseleave="setHover(null)"
                                            ><Button
                                                v-if="
                                                    rowIdx === 0 &&
                                                    getTlContacts(branch)
                                                        .length === 0
                                                "
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7 text-amber-400 hover:bg-amber-50"
                                                @click="handleAddStcTlContact(branch, 'TL')"
                                                ><UserPlus
                                                    class="h-3.5 w-3.5" /></Button
                                        ></TableCell>
                                    </template>
                                </TableRow>
                            </template>
                        </template>
                    </template>
                </TableBody>
            </Table>
        </div>
    </TooltipProvider>
</template>
