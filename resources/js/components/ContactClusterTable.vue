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
import { Pencil, Copy } from 'lucide-vue-next';
import { ref } from 'vue';

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

    const isValid = /^(?:62|0)?8\d{9,12}$/.test(cleaned);
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

const copiedId = ref<number | null>(null);

const copyPhone = async (phone: string, id: number) => {
    if (!phone) return;

    await navigator.clipboard.writeText(phone);
    copiedId.value = id;

    setTimeout(() => {
        copiedId.value = null;
    }, 1500);
};
</script>

<template>
    <div class="rounded-lg border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead rowspan="2" class="w-64 border-r align-middle">
                    </TableHead>

                    <!-- RLQH -->
                    <TableHead
                        colspan="4"
                        class="border-r bg-emerald-50 text-center font-extrabold"
                    >
                        RLQH / ALQH
                    </TableHead>

                    <!-- CLQH -->
                    <TableHead colspan="4" class="bg-blue-50 text-center font-extrabold">
                        CLQH
                    </TableHead>
                </TableRow>

                <TableRow>
                    <!-- Sub header RLQH -->
                    <TableHead class="border-t">Nama</TableHead>
                    <TableHead class="border-t">NIP</TableHead>
                    <TableHead class="border-t">Kontak</TableHead>
                    <TableHead class="w-3.5 border-t border-r"></TableHead>
                    <!-- Sub header CLQH -->
                    <TableHead class="border-t">Nama</TableHead>
                    <TableHead class="border-t">NIP</TableHead>
                    <TableHead class="border-t">Kontak</TableHead>
                    <TableHead class="w-3.5 border-t"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <!-- BARIS REGIONAL -->
                <template v-for="regional in regionals" :key="regional.id">
                    <TableRow class="group bg-primary/10 hover:bg-primary/20">
                        <!-- Regional Name -->
                        <TableCell class="border-r font-extrabold uppercase">
                            {{ regional.name }}
                        </TableCell>

                        <!-- RLQH AREA -->
                        <!-- NAMA RLQH (Regional) -->
                        <TableCell class="border-r font-bold">{{
                            regional.contact_cluster?.name
                        }}</TableCell>

                        <!-- NIP RLQH (Regional) -->
                        <TableCell class="border-r font-bold">{{
                            regional.contact_cluster?.nip
                        }}</TableCell>

                        <!-- PHONE RLQH (Regional) -->
                        <TableCell class="font-bold">
                            <div class="flex items-center gap-1">
                                <a :href="createWhatsappLink(regional.contact_cluster?.phone || null)"
                                target="_blank"
                               class="hover:text-blue-600"
                            >
                                {{ regional.contact_cluster?.phone }}
                            </a>
                            <button
                                v-if="regional.contact_cluster?.phone"
                                type="button"
                                @click="copyPhone(regional.contact_cluster?.phone || '', regional.id)"
                                class="group visible md:invisible relative flex h-6 w-6 items-center justify-center rounded-md border border-transparent md:group-hover:visible hover:border-border hover:bg-muted active:scale-95"
                            >
                                <Copy class="h-3.5 w-3.5 text-muted-foreground group-hover:text-foreground" />
                                <span v-if="copiedId === regional.id"
                                    class="absolute -top-7 rounded bg-foreground px-2 py-0.5 text-xs text-background shadow"
                                >
                                    Copied ✓
                                </span>
                            </button>
                            </div>
                        </TableCell>

                        <!-- EDIT RLQH (Regional) -->
                        <TableCell class="border-r">
                            <div class="flex items-center justify-center h-full w-full">
                                <button
                                    class="visible md:invisible cursor-pointer font-medium text-blue-600 md:group-hover:visible hover:underline"
                                    @click="editRegionalContact(regional, regional.contact_cluster)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                            </div>
                        </TableCell>

                        <!-- CLQH Regional (kosong) -->
                        <TableCell></TableCell>
                        <TableCell></TableCell>
                        <TableCell></TableCell>
                        <TableCell></TableCell>
                    </TableRow>

                    <!-- BARIS AREA -->
                    <template v-for="area in regional.areas" :key="area.id">
                        <TableRow class="group">
                            <TableCell class="border-r pl-4 font-medium">{{
                                area.name
                            }}</TableCell>
                            <TableCell
                                class="border-r"
                                :rowspan="area.branches.length + 1"
                                >{{ area.contact_cluster?.name }}</TableCell
                            >
                            <TableCell
                                class="border-r"
                                :rowspan="area.branches.length + 1"
                                >{{ area.contact_cluster?.nip }}</TableCell
                            >
                            <TableCell
                                class="border-r"
                                :rowspan="area.branches.length + 1"
                                >
                                <div class="flex items-center gap-1">
                                    <a :href="createWhatsappLink(area.contact_cluster?.phone || null)"
                                        target="_blank"
                                       class="hover:text-blue-600"
                                    >
                                        {{ area.contact_cluster?.phone }}
                                    </a>
                                    <button
                                        v-if="area.contact_cluster?.phone"
                                        type="button"
                                        @click="copyPhone(area.contact_cluster?.phone || '', area.id)"
                                        class="group visible md:invisible relative flex h-6 w-6 items-center justify-center rounded-md border border-transparent md:group-hover:visible hover:border-border hover:bg-muted active:scale-95"
                                    >
                                        <Copy
                                            class="h-3.5 w-3.5 text-muted-foreground group-hover:text-foreground"
                                        />
                                        <span v-if="copiedId === area.id"
                                              class="absolute -top-7 rounded bg-foreground px-2 py-0.5 text-xs text-background shadow">
                                            Copied ✓
                                        </span>
                                    </button>
                                </div>
                            </TableCell
                            >
                            <TableCell
                                class="border-r"
                                :rowspan="area.branches.length + 1"
                                >
                                <div class="flex items-center justify-center h-full w-full">
                                    <button
                                        class="visible md:invisible cursor-pointer font-medium text-blue-600 group-hover:visible hover:underline"
                                        @click="editAreaContact(area, area.contact_cluster)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </div>
                            </TableCell>
                            <TableCell />
                            <TableCell />
                            <TableCell />
                            <TableCell />
                        </TableRow>

                        <template
                            v-for="branch in area.branches"
                            :key="branch.id"
                        >
                            <TableRow class="group">
                                <TableCell class="border-r pl-6">{{
                                    branch.name
                                }}</TableCell>
                                <TableCell class="border-r">{{
                                    branch.contact_cluster?.name
                                }}</TableCell>
                                <TableCell class="border-r">{{
                                    branch.contact_cluster?.nip
                                }}</TableCell>
                                <TableCell class="border-r">
                                    <div class="flex items-center gap-1">
                                        <a :href="createWhatsappLink(branch.contact_cluster?.phone || null)"
                                            target="_blank"
                                           class="hover:text-blue-600"
                                        >
                                            {{ branch.contact_cluster?.phone }}
                                        </a>
                                        <button
                                            v-if="branch.contact_cluster?.phone"
                                            type="button"
                                            @click="copyPhone(branch.contact_cluster?.phone || '', branch.id)"
                                            class="group visible md:invisible relative flex h-6 w-6 items-center justify-center rounded-md border border-transparent group-hover:visible hover:border-border hover:bg-muted active:scale-95"
                                        >
                                            <Copy
                                                class="h-3.5 w-3.5 text-muted-foreground group-hover:text-foreground"
                                            />
                                            <span
                                                v-if="copiedId === branch.id"
                                                class="absolute -top-7 rounded bg-foreground px-2 py-0.5 text-xs text-background shadow"
                                            >
                                                Copied ✓
                                            </span>
                                        </button>
                                    </div>
                                </TableCell>
                                <TableCell class="border-r">
                                    <div class="flex items-center justify-center h-full w-full">
                                        <button
                                            @click="editBranchContact(branch, branch.contact_cluster)"
                                            class="visible md:invisible cursor-pointer font-medium text-blue-600 group-hover:visible hover:underline"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </template>

                    <!-- Branch Direct Regional -->
                    <template
                        v-for="branch in regional.branches"
                        :key="branch.id"
                    >
                        <TableRow class="group">
                            <TableCell class="border-r pl-3">{{
                                branch.name
                            }}</TableCell>
                            <TableCell />
                            <TableCell />
                            <TableCell />
                            <TableCell class="border-r" />
                            <TableCell class="border-r">{{
                                branch.contact_cluster?.name
                            }}</TableCell>
                            <TableCell class="border-r">{{
                                branch.contact_cluster?.nip
                            }}</TableCell>
                            <TableCell class="border-r">
                                <div class="flex items-center gap-1">
                                    <a :href="createWhatsappLink(branch.contact_cluster?.phone || '')"
                                        target="_blank"
                                       class="hover:text-blue-600"
                                    >
                                        {{ branch.contact_cluster?.phone }}
                                    </a>
                                    <button
                                        v-if="branch.contact_cluster?.phone"
                                        type="button"
                                        @click="copyPhone(branch.contact_cluster?.phone || '', branch.id)"
                                        class="group visible md:invisible relative flex h-6 w-6 items-center justify-center rounded-md border border-transparent group-hover:visible hover:border-border hover:bg-muted active:scale-95"
                                    >
                                        <Copy class="h-3.5 w-3.5 text-muted-foreground group-hover:text-foreground" />
                                        <span v-if="copiedId === branch.id"
                                              class="absolute -top-7 rounded bg-foreground px-2 py-0.5 text-xs text-background shadow"
                                        >
                                            Copied ✓
                                        </span>
                                    </button>
                                </div>
                            </TableCell>
                            <TableCell class="border-r">
                                <div class="flex items-center justify-center h-full w-full">
                                    <button
                                        @click="editBranchContact(branch, branch.contact_cluster)"
                                        class="visible md:invisible cursor-pointer font-medium text-blue-600 group-hover:visible hover:underline"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>

                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
