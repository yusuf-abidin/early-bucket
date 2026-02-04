<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import etape from '@/routes/etape';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Check, X } from 'lucide-vue-next';

const props = defineProps<{
    performance_etapes?: any;
    nasional?: {
        total_prognosa: number;
        total_branches: number;
    };
    categories: {
        komitmen_etape: any[];
        komitmen_eom_bc: any[];
        komitmen_eom_bm: any[];
    };
    users?: { id: number; name: string }[];
    currentYear?: number;
    currentMonth?: number;
    currentEtape?: number;
}>();

const editingId = ref<number | null>(null);
const editForm = ref<any>({});

const startEdit = (item: any) => {
    if (editingId.value === item.branch_id) return;
    editingId.value = item.branch_id;
    editForm.value = { ...item };
};

const handleSave = () => {
    router.post(etape.store().url, editForm.value, {
        onSuccess: () => {
            editingId.value = null;
            editForm.value = {};
        },
        onError: (errors) => {
            console.error('Save error:', errors);
        },
        preserveScroll: true,
    });
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.value = {};
};

const handleGlobalEsc = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && editingId.value !== null) {
        cancelEdit();
    }

    if (e.key === 'Enter' && editingId.value !== null) {
        handleSave();
    }
};

const formatAngkaPrognosa = (nilai: number | null | undefined): string => {
    if (nilai === null || nilai === undefined) return '-';
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(nilai);
};

const handleAreaPIC = (areaId: number, userId: number) => {
    const area = props.performance_etapes?.find((a: any) => a.id === areaId);
    if (!area || !area.branches) return;

    const performanceData = area.branches.map((branch: any) => ({
        branch_id: branch.branch_id,
        etape_no: props.currentEtape || branch.etape_no,
        year: props.currentYear || branch.year,
        month: props.currentMonth || branch.month,
        user_id: userId,
        komitmen_etape_id: branch.komitmen_etape_id,
        komitmen_eom_bc_id: branch.komitmen_eom_bc_id,
        komitmen_eom_bm_id: branch.komitmen_eom_bm_id,
        prognosa_akhir_bulan: branch.prognosa_akhir_bulan,
        kendala: branch.kendala,
    }));

    router.post(
        '/etape/bulk',
        { performance: performanceData },
        {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Bulk update error:', errors);
            },
        },
    );
};

onMounted(() => {
    window.addEventListener('keydown', handleGlobalEsc);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalEsc);
});
</script>

<template>
    <div class="overflow-x-auto rounded-md border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[200px] min-w-[200px] font-bold">
                        Area / Cabang
                    </TableHead>
                    <TableHead class="w-[180px] min-w-[180px] font-bold"
                        >PIC</TableHead
                    >
                    <TableHead class="w-[160px] min-w-[160px] font-bold">
                        Komitmen Etape
                    </TableHead>
                    <TableHead class="w-[160px] min-w-[160px] font-bold">
                        Komitmen EOM (BC)
                    </TableHead>
                    <TableHead class="w-[160px] min-w-[160px] font-bold">
                        Komitmen EOM (BM)
                    </TableHead>
                    <TableHead class="w-[180px] min-w-[180px] font-bold">
                        Prognosa Akhir Bulan
                    </TableHead>
                    <TableHead class="min-w-[250px] font-bold"
                        >Kendala</TableHead
                    >
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-if="
                        !props.performance_etapes ||
                        props.performance_etapes.length === 0
                    "
                >
                    <TableCell
                        :colspan="7"
                        class="h-24 text-center text-muted-foreground"
                    >
                        Tidak ditemukan data ETAPE
                    </TableCell>
                </TableRow>

                <template
                    v-for="area in props.performance_etapes"
                    :key="area.id"
                >
                    <!-- Area Header Row -->
                    <TableRow class="bg-muted/50 hover:bg-muted/50">
                        <TableCell class="font-semibold">
                            {{ area.name }}
                        </TableCell>
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 gap-2"
                                    >
                                        <span class="hidden sm:inline">
                                            Pilih PIC Area
                                        </span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56">
                                    <DropdownMenuLabel>
                                        Pilih PIC untuk Area
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        v-for="user in props.users"
                                        :key="user.id"
                                        @click="handleAreaPIC(area.id, user.id)"
                                        class="cursor-pointer"
                                    >
                                        {{ user.name }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                        <TableCell
                            colspan="3"
                            class="text-sm text-muted-foreground"
                        >
                            {{ area.branches?.length || 0 }} cabang
                        </TableCell>
                        <TableCell
                            class="text-right font-mono font-semibold tabular-nums"
                        >
                            {{ formatAngkaPrognosa(area.total_prognosa) }}
                        </TableCell>
                        <TableCell></TableCell>
                    </TableRow>

                    <!-- Branch Rows -->
                    <TableRow
                        v-for="branch in area.branches"
                        :key="branch.branch_id"
                        :class="{
                            'bg-muted/20': branch.is_new,
                            'bg-accent/50': editingId === branch.branch_id,
                        }"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <!-- Branch Name -->
                        <TableCell
                            class="cursor-pointer pl-8 font-medium"
                            @click="startEdit(branch)"
                        >
                            {{ branch.branch_name }}
                        </TableCell>

                        <!-- PIC -->
                        <TableCell @click="startEdit(branch)">
                            <div
                                v-if="editingId === branch.branch_id"
                                @click.stop
                                class="w-full"
                            >
                                <Select v-model="editForm.user_id">
                                    <SelectTrigger class="h-9 w-full">
                                        <SelectValue placeholder="Pilih PIC" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="user in props.users"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            {{ user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div v-else class="cursor-pointer">
                                <Badge
                                    v-if="branch.user"
                                    :class="branch.user.color?.class"
                                    class="font-normal"
                                >
                                    {{ branch.user_name }}
                                </Badge>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    -
                                </span>
                            </div>
                        </TableCell>

                        <!-- Komitmen Etape -->
                        <TableCell @click="startEdit(branch)">
                            <div
                                v-if="editingId === branch.branch_id"
                                @click.stop
                                class="w-full"
                            >
                                <Select v-model="editForm.komitmen_etape_id">
                                    <SelectTrigger class="h-9 w-full">
                                        <SelectValue
                                            placeholder="Pilih Komitmen"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="komitmen in props.categories
                                                .komitmen_etape"
                                            :key="komitmen.id"
                                            :value="komitmen.id"
                                        >
                                            {{ komitmen.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div v-else class="cursor-pointer">
                                <Badge
                                    v-if="branch.komitmen_etape"
                                    :class="branch.komitmen_etape.color?.class"
                                    class="font-normal"
                                >
                                    {{ branch.komitmen_etape.name }}
                                </Badge>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    -
                                </span>
                            </div>
                        </TableCell>

                        <!-- Komitmen EOM BC -->
                        <TableCell @click="startEdit(branch)">
                            <div
                                v-if="editingId === branch.branch_id"
                                @click.stop
                                class="w-full"
                            >
                                <Select v-model="editForm.komitmen_eom_bc_id">
                                    <SelectTrigger class="h-9 w-full">
                                        <SelectValue
                                            placeholder="Pilih Komitmen"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="komitmen in props.categories
                                                .komitmen_eom_bc"
                                            :key="komitmen.id"
                                            :value="komitmen.id"
                                        >
                                            {{ komitmen.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div v-else class="cursor-pointer">
                                <Badge
                                    v-if="branch.komitmen_eom_bc"
                                    :class="branch.komitmen_eom_bc.color?.class"
                                    class="font-normal"
                                >
                                    {{ branch.komitmen_eom_bc.name }}
                                </Badge>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    -
                                </span>
                            </div>
                        </TableCell>

                        <!-- Komitmen EOM BM -->
                        <TableCell @click="startEdit(branch)">
                            <div
                                v-if="editingId === branch.branch_id"
                                @click.stop
                                class="w-full"
                            >
                                <Select v-model="editForm.komitmen_eom_bm_id">
                                    <SelectTrigger class="h-9 w-full">
                                        <SelectValue
                                            placeholder="Pilih Komitmen"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="komitmen in props.categories
                                                .komitmen_eom_bm"
                                            :key="komitmen.id"
                                            :value="komitmen.id"
                                        >
                                            {{ komitmen.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div v-else class="cursor-pointer">
                                <Badge
                                    v-if="branch.komitmen_eom_bm"
                                    :class="branch.komitmen_eom_bm.color?.class"
                                    class="font-normal"
                                >
                                    {{ branch.komitmen_eom_bm.name }}
                                </Badge>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    -
                                </span>
                            </div>
                        </TableCell>

                        <!-- Prognosa Akhir Bulan -->
                        <TableCell
                            class="text-right"
                            @click="startEdit(branch)"
                        >
                            <div
                                v-if="editingId === branch.branch_id"
                                @click.stop
                                class="w-full"
                            >
                                <Input
                                    v-model="editForm.prognosa_akhir_bulan"
                                    type="number"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="h-9 w-full"
                                />
                            </div>
                            <div
                                v-else
                                class="cursor-pointer font-mono text-sm tabular-nums"
                            >
                                {{
                                    formatAngkaPrognosa(
                                        branch.prognosa_akhir_bulan,
                                    )
                                }}
                            </div>
                        </TableCell>

                        <!-- Kendala -->
                        <TableCell>
                            <div
                                v-if="editingId === branch.branch_id"
                                @click.stop
                                class="flex items-start gap-2"
                            >
                                <Textarea
                                    v-model="editForm.kendala"
                                    placeholder="Masukkan kendala..."
                                    class="min-h-[60px] flex-1 resize-none text-sm"
                                    rows="2"
                                    @keydown.enter.stop
                                />
                                <div class="flex flex-col gap-1.5">
                                    <Button
                                        @click="handleSave"
                                        size="icon"
                                        class="h-7 w-7 bg-green-600 hover:bg-green-700"
                                        title="Simpan (Enter)"
                                    >
                                        <Check class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        @click="cancelEdit"
                                        size="icon"
                                        variant="destructive"
                                        class="h-7 w-7"
                                        title="Batal (Esc)"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <div
                                v-else
                                @click="startEdit(branch)"
                                class="cursor-pointer"
                            >
                                <div
                                    v-if="branch.kendala"
                                    class="line-clamp-2 text-sm"
                                    :title="branch.kendala"
                                >
                                    {{ branch.kendala }}
                                </div>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    -
                                </span>
                            </div>
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
            <TableFooter v-if="props.nasional">
                <TableRow>
                    <TableCell class="text-lg"> NASIONAL </TableCell>
                    <TableCell></TableCell>
                    <TableCell class="text-sm text-muted-foreground">
                    </TableCell>
                    <TableCell></TableCell>
                    <TableCell></TableCell>
                    <TableCell
                        class="text-right font-mono text-lg tabular-nums"
                    >
                        {{ formatAngkaPrognosa(props.nasional.total_prognosa) }}
                    </TableCell>
                    <TableCell></TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </div>

    <!-- Helper Text -->
    <div v-if="editingId !== null" class="mt-2 text-xs text-muted-foreground">
        💡 Tips: Tekan
        <kbd class="rounded border bg-muted px-1">Enter</kbd> untuk menyimpan,
        <kbd class="rounded border bg-muted px-1">Esc</kbd> untuk membatalkan
    </div>
</template>

<style scoped>
/* Smooth transitions */
.transition-colors {
    transition: background-color 0.15s ease-in-out;
}

/* Ensure consistent cell heights */
td {
    vertical-align: middle;
}

/* Line clamp untuk text truncation */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Keyboard shortcuts styling */
kbd {
    font-family:
        ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.75rem;
}
</style>
