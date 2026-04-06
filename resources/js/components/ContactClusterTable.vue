<script setup lang="ts">
import {
    Area,
    Branch,
    ContactCluster,
    EditContactPayload,
    Regional,
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
import {
    TooltipProvider,
} from '@/components/ui/tooltip';
import { Building2, MapPin, Landmark } from 'lucide-vue-next';
import { ref} from 'vue';
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

const createWhatsappLink = (phone: string | null, message?: string): string | undefined => {
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
</script>

<template>
    <TooltipProvider :delay-duration="200">
        <div class="overflow-hidden rounded-xl border border-border shadow-sm">
            <Table>
                <!-- ── HEADER ─────────────────────────────────────────────── -->
                <TableHeader>
                    <!-- Row 1 – group labels -->
                    <TableRow class="border-b-0">
                        <TableHead
                            rowspan="2"
                            class="border-r text-center font-semibold uppercase tracking-wider"
                        >
                            Wilayah
                        </TableHead>

                        <!-- RLQH / ALQH group -->
                        <TableHead
                            colspan="4"
                            class="border-b border-r border-border bg-emerald-50/80 text-center"
                        >
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="font-bold uppercase tracking-widest">
                                    RLQH / ALQH
                                </span>
                            </div>
                        </TableHead>

                        <!-- CLQH group -->
                        <TableHead
                            colspan="4"
                            class="border-b bg-sky-50/80 text-center"
                        >
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="inline-block h-2 w-2 rounded-full bg-sky-500"></span>
                                <span class="font-bold uppercase tracking-widest">
                                    CLQH
                                </span>
                            </div>
                        </TableHead>
                    </TableRow>

                    <!-- Row 2 – column sub-labels -->
                    <TableRow class="bg-muted/20">
                        <!-- RLQH sub-cols -->
                        <TableHead class="border-r text-center">Nama</TableHead>
                        <TableHead class="border-r text-center">NIP</TableHead>
                        <TableHead class="border-r text-center">Kontak</TableHead>
                        <TableHead class="w-10 border-r"></TableHead>
                        <!-- CLQH sub-cols -->
                        <TableHead class="border-r text-center">Nama</TableHead>
                        <TableHead class="border-r text-center">NIP</TableHead>
                        <TableHead class="border-r text-center">Kontak</TableHead>
                        <TableHead class="w-10 text-xs"></TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-for="regional in regionals" :key="regional.id">

                        <!-- ── REGIONAL ROW ──────────────────────────────── -->
                        <TableRow class="group border-t-2 border-border bg-muted/30 transition-colors">
                            <!-- Level label -->
                            <TableCell class="border-r border-border py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                        <Landmark class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <Badge variant="secondary" class="mb-0.5 px-1.5 py-0 text-[10px] font-semibold uppercase tracking-wider">
                                            Regional
                                        </Badge>
                                        <p class="text-sm font-bold leading-tight text-foreground">
                                            {{ regional.name }}
                                        </p>
                                    </div>
                                </div>
                            </TableCell>

                            <!-- RLQH – Regional -->
                            <TableCell class="border-r border-border/40 py-3 text-center">
                                <ContactNameCell :name="regional.contact_cluster?.name" />
                            </TableCell>
                            <TableCell class="border-r border-border/40 py-3 text-center">
                                <NipCell :nip="regional.contact_cluster?.nip" />
                            </TableCell>
                            <TableCell class="border-r border-border/40 py-3 text-center">
                                <PhoneCell
                                    :phone="regional.contact_cluster?.phone"
                                    :wa-link="createWhatsappLink(regional.contact_cluster?.phone || null)"
                                    :entity-id="regional.id"
                                    :copied-id="copiedId"
                                    @copy="copyPhone"
                                />
                            </TableCell>
                            <TableCell class="border-r border-border py-3">
                                <EditCell @edit="editRegionalContact(regional, regional.contact_cluster)" />
                            </TableCell>

                            <!-- CLQH – empty for Regional row -->
                            <TableCell /><TableCell /><TableCell /><TableCell />
                        </TableRow>

                        <!-- ── AREA ROWS ─────────────────────────────────── -->
                        <template v-for="area in regional.areas" :key="area.id">
                            <TableRow class="group border-t border-border/60 transition-colors">
                                <!-- Area name -->
                                <TableCell class="border-r border-border/40 py-2.5">
                                    <div class="flex items-center gap-2 pl-4">
                                        <div class="h-full w-0.5 shrink-0 self-stretch rounded-full bg-border"></div>
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                                            <MapPin class="h-3 w-3" />
                                        </div>
                                        <div>
                                            <Badge variant="outline" class="mb-0.5 border-sky-200 px-1.5 py-0 text-[10px] font-semibold uppercase tracking-wider text-sky-700">
                                                Area
                                            </Badge>
                                            <p class="text-sm font-semibold">{{ area.name }}</p>
                                        </div>
                                    </div>
                                </TableCell>

                                <!-- RLQH – Area (rowspan) -->
                                <TableCell
                                    class="border-r border-border/40 py-2.5 align-middle text-center"
                                    :rowspan="area.branches.length + 1"
                                >
                                    <ContactNameCell :name="area.contact_cluster?.name" />
                                </TableCell>
                                <TableCell
                                    class="border-r border-border/40 py-2.5 align-middle text-center"
                                    :rowspan="area.branches.length + 1"
                                >
                                    <NipCell :nip="area.contact_cluster?.nip" />
                                </TableCell>
                                <TableCell
                                    class="border-r border-border/40 py-2.5 align-middle text-center"
                                    :rowspan="area.branches.length + 1"
                                >
                                    <PhoneCell
                                        :phone="area.contact_cluster?.phone"
                                        :wa-link="createWhatsappLink(area.contact_cluster?.phone || null)"
                                        :entity-id="area.id"
                                        :copied-id="copiedId"
                                        @copy="copyPhone"
                                    />
                                </TableCell>
                                <TableCell
                                    class="border-r border-border py-2.5 align-middle"
                                    :rowspan="area.branches.length + 1"
                                >
                                    <EditCell @edit="editAreaContact(area, area.contact_cluster)" />
                                </TableCell>

                                <!-- CLQH – empty -->
                                <TableCell /><TableCell /><TableCell /><TableCell />
                            </TableRow>

                            <!-- Branch rows inside Area -->
                            <template v-for="branch in area.branches" :key="branch.id">
                                <TableRow class="group border-t border-dashed border-border/40 transition-colors">
                                    <TableCell class="border-r border-border/40 py-2">
                                        <div class="flex items-center gap-2 pl-10">
                                            <div class="h-full w-0.5 shrink-0 self-stretch rounded-full bg-border/50"></div>
                                            <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-muted text-muted-foreground">
                                                <Building2 class="h-2.5 w-2.5" />
                                            </div>
                                            <p class="text-sm text-foreground/90">{{ branch.name }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell class="border-r border-border/40 py-2 text-center">
                                        <ContactNameCell :name="branch.contact_cluster?.name" />
                                    </TableCell>
                                    <TableCell class="border-r border-border/40 py-2 text-center">
                                        <NipCell :nip="branch.contact_cluster?.nip" />
                                    </TableCell>
                                    <TableCell class="border-r border-border/40 py-2 text-center">
                                        <PhoneCell
                                            :phone="branch.contact_cluster?.phone"
                                            :wa-link="createWhatsappLink(branch.contact_cluster?.phone || null)"
                                            :entity-id="branch.id"
                                            :copied-id="copiedId"
                                            @copy="copyPhone"
                                        />
                                    </TableCell>
                                    <TableCell class="border-r border-border py-2">
                                        <EditCell @edit="editBranchContact(branch, branch.contact_cluster)" />
                                    </TableCell>
                                </TableRow>
                            </template>
                        </template>

                        <!-- ── BRANCH DIRECT TO REGIONAL ────────────────── -->
                        <template v-for="branch in regional.branches" :key="branch.id">
                            <TableRow class="group border-t border-dashed border-border/40 transition-colors">
                                <TableCell class="border-r border-border/40 py-2">
                                    <div class="flex items-center gap-2 pl-4">
                                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-muted text-muted-foreground">
                                            <Building2 class="h-2.5 w-2.5" />
                                        </div>
                                        <p class="text-sm text-foreground/90">{{ branch.name }}</p>
                                    </div>
                                </TableCell>
                                <!-- RLQH – empty -->
                                <TableCell /><TableCell /><TableCell />
                                <TableCell class="border-r border-border" />
                                <!-- CLQH -->
                                <TableCell class="border-r border-border/40 py-2 text-center">
                                    <ContactNameCell :name="branch.contact_cluster?.name" />
                                </TableCell>
                                <TableCell class="border-r border-border/40 py-2 text-center">
                                    <NipCell :nip="branch.contact_cluster?.nip" />
                                </TableCell>
                                <TableCell class="border-r border-border/40 py-2 text-center">
                                    <PhoneCell
                                        :phone="branch.contact_cluster?.phone"
                                        :wa-link="createWhatsappLink(branch.contact_cluster?.phone || '')"
                                        :entity-id="branch.id"
                                        :copied-id="copiedId"
                                        @copy="copyPhone"
                                    />
                                </TableCell>
                                <TableCell class="py-2">
                                    <EditCell @edit="editBranchContact(branch, branch.contact_cluster)" />
                                </TableCell>
                            </TableRow>
                        </template>
                    </template>
                </TableBody>
            </Table>
        </div>
    </TooltipProvider>
</template>
