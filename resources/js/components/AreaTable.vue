<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Search,
    Pencil,
    Trash2,
    Check,
    X,
    ChevronRight,
    ChevronDown,
    Plus,
} from 'lucide-vue-next';
import { Area, Branch, Regional } from '@/types';
import { router } from '@inertiajs/vue3';
import admin from '@/routes/admin';
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from '@/components/ui/hover-card';

// Props definition
const props = defineProps<{
    regionals: Regional[];
    search?: string;
}>();

const selectedRegional = defineModel<Regional | null>('selectedRegional', {
    default: null,
});
const selectedArea = defineModel<Area | null>('selectedArea', {
    default: null,
});
const selectedBranch = defineModel<Branch | null>('selectedBranch', {
    default: null,
});
const formRegionalIsOpen = defineModel<boolean>('formRegionalIsOpen', {
    default: false,
});
const formAreaIsOpen = defineModel<boolean>('formAreaIsOpen', {
    default: false,
});
const formBranchIsOpen = defineModel<boolean>('formBranchIsOpen', {
    default: false,
});
const dialogDeleteRegional = defineModel<boolean>('dialogDeleteRegional', {
    default: false,
});
const dialogDeleteArea = defineModel<boolean>('dialogDeleteArea', {
    default: false,
});
const dialogDeleteBranch = defineModel<boolean>('dialogDeleteBranch', {
    default: false,
});
const addFromRegional = defineModel<number | null>('addFromRegional', {
    default: null,
});
const addFromfromArea = defineModel<number | null>('addFromArea', {
    default: null,
});

const handleFormRegional = (regional: Regional | null = null) => {
    selectedRegional.value = regional;
    formRegionalIsOpen.value = true;
    hoverAddButton.value = false;
};

const handleFormArea = (area: Area | null = null) => {
    selectedArea.value = area;
    formAreaIsOpen.value = true;
    hoverAddButton.value = false;
};

const handleFormBranch = (branch: Branch | null = null, fromRegional: number | null = null, fromArea: number | null = null) => {
    if (fromRegional !== null) {
        addFromRegional.value = fromRegional;
    }
    if (fromArea !== null) {
        addFromfromArea.value = fromArea;
    }
    selectedBranch.value = branch;
    formBranchIsOpen.value = true;
    hoverAddButton.value = false;
};

const handleDeleteRegional = (regional: Regional) => {
    selectedRegional.value = regional;
    dialogDeleteRegional.value = true;
};

const handleDeleteArea = (area: Area) => {
    selectedArea.value = area;
    dialogDeleteArea.value = true;
};

const handleDeleteBranch = (branch: Branch) => {
    selectedBranch.value = branch;
    dialogDeleteBranch.value = true;
};

// -------------------------
// Expand/Collapse State
// Level regional dan level area masing-masing punya state sendiri
// -------------------------
const expandedRegionals = ref<number[]>(
    props.regionals?.map((r) => r.id) ?? [],
);
const expandedAreas = ref<number[]>(
    props.regionals?.flatMap((r) => r.areas?.map((a) => a.id) ?? []) ?? [],
);

const allRegionalsExpanded = computed(
    () => expandedRegionals.value.length === props.regionals.length,
);

const toggleRegional = (regionalId: number) => {
    const index = expandedRegionals.value.indexOf(regionalId);
    if (index > -1) {
        expandedRegionals.value.splice(index, 1);
    } else {
        expandedRegionals.value.push(regionalId);
    }
};

const toggleArea = (areaId: number) => {
    const index = expandedAreas.value.indexOf(areaId);
    if (index > -1) {
        expandedAreas.value.splice(index, 1);
    } else {
        expandedAreas.value.push(areaId);
    }
};

const toggleExpandAll = () => {
    if (allRegionalsExpanded.value) {
        expandedRegionals.value = [];
        expandedAreas.value = [];
    } else {
        expandedRegionals.value = props.regionals.map((r) => r.id);
        expandedAreas.value = props.regionals.flatMap(
            (r) => r.areas?.map((a) => a.id) ?? [],
        );
    }
};

// -------------------------
// Inline edit area
// -------------------------
const editingRegionalId = ref<number | null>(null);
const editName = ref('');
const regionalInputRefs = ref<Record<number, any>>({});

const startEditRegional = async (regional: Regional) => {
    editingRegionalId.value = regional.id;
    editName.value = regional.name;

    await nextTick();

    const component = regionalInputRefs.value[regional.id];
    if (component) {
        const inputElement =
            component.$el instanceof HTMLInputElement
                ? component.$el
                : component.$el?.querySelector('input');
        if (inputElement) inputElement.focus();
    }
};

const cancelEdit = () => {
    editingRegionalId.value = null;
    editName.value = '';
};

const handleUpdateRegional = (regional: Regional) => {
    if (!editName.value.trim()) return;

    router.patch(
        admin.regionals.update(regional.id).url,
        { name: editName.value },
        {
            onSuccess: () => {
                editingRegionalId.value = null;
            },
        },
    );
};

// -------------------------
// Hitung total cabang per regional (area.branches + direct branches)
// -------------------------
const totalBranchesInRegional = (regional: Regional): number => {
    const fromAreas = regional.areas?.reduce(
        (sum, area) => sum + (area.branches?.length ?? 0),
        0,
    ) ?? 0;
    const direct = regional.branches?.length ?? 0;
    return fromAreas + direct;
};

// -------------------------
// Search
// -------------------------
const searchQuery = ref(props.search || '');
const applyFilters = () => {
    router.get(
        window.location.pathname,
        { search: searchQuery.value || undefined },
        { preserveState: true, replace: true },
    );
};

const hoverAddButton = ref<boolean>(false);
</script>

<template>
    <!-- Toolbar -->
    <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex w-full items-center gap-2 sm:max-w-sm">
            <div class="relative flex-1">
                <Search class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground" />
                <Input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari regional, area, atau cabang"
                    class="w-full min-w-[150px] pl-9"
                    @keyup.enter="applyFilters"
                />
            </div>
            <Button variant="secondary" size="sm" @click="applyFilters" class="cursor-pointer">
                Cari
            </Button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Button
                variant="secondary"
                @click="toggleExpandAll"
                class="flex-1 cursor-pointer sm:flex-none"
            >
                {{ allRegionalsExpanded ? 'Collapse All' : 'Expand All' }}
            </Button>

            <HoverCard v-model:open="hoverAddButton" :open-delay="300">
                <HoverCardTrigger as-child>
                    <Button class="flex-1 cursor-pointer gap-2 sm:flex-none">
                        <Plus class="h-4 w-4" />
                        Tambah
                    </Button>
                </HoverCardTrigger>
                <HoverCardContent align="end" side="bottom" :side-offset="8" class="flex w-40 flex-col gap-1">
                    <Button class="cursor-pointer" variant="ghost" @click="handleFormRegional()">
                        Regional
                    </Button>
                    <Button class="cursor-pointer" variant="ghost" @click="handleFormArea()">
                        Area
                    </Button>
                    <Button class="cursor-pointer" variant="ghost" @click="handleFormBranch()">
                        Cabang
                    </Button>
                </HoverCardContent>
            </HoverCard>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-md border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[30px]"></TableHead>
                    <TableHead class="font-bold">Nama</TableHead>
                    <TableHead class="w-[130px] text-center font-bold">Total Cabang</TableHead>
                    <TableHead class="w-[120px] text-right font-bold">Aksi</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <!-- Empty state -->
                <TableRow v-if="regionals.length === 0">
                    <TableCell colspan="4" class="py-10 text-center text-muted-foreground">
                        Data tidak ditemukan.
                    </TableCell>
                </TableRow>

                <template v-for="regional in regionals" :key="regional.id">
                    <!-- ===== ROW: REGIONAL ===== -->
                    <TableRow
                        class="cursor-pointer bg-muted/20 hover:bg-muted/40"
                        @click="toggleRegional(regional.id)"
                    >
                        <TableCell>
                            <ChevronDown
                                v-if="expandedRegionals.includes(regional.id)"
                                class="h-4 w-4 text-gray-500"
                            />
                            <ChevronRight v-else class="h-4 w-4 text-gray-500" />
                        </TableCell>
                        <TableCell>
                            <div @click.stop>
                                <div
                                 v-if="editingRegionalId === regional.id"
                                 class="flex items-center gap-2"
                                >
                                    <Input
                                        v-model="editName"
                                        :ref="(el) => (regionalInputRefs[regional.id] = el)"
                                        @keyup.enter="handleUpdateRegional(regional)"
                                        @keyup.esc="cancelEdit"
                                        class="h-8"
                                    />
                                </div>
                            <span v-else class="font-extrabold text-sm">{{ regional.name }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="text-center">
                            <Badge variant="secondary" class="font-mono">
                                {{ totalBranchesInRegional(regional) }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right" @click.stop>
                            <div class="flex justify-end gap-1">
                                <template v-if="editingRegionalId !== regional.id">
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="cursor-pointer"
                                        title="Edit Regional"
                                        @click="startEditRegional(regional)"
                                    >
                                        <Pencil class="h-4 w-4 text-blue-600" />
                                    </Button>
                                    <Button
                                        v-if="totalBranchesInRegional(regional) === 0 && (regional.areas?.length ?? 0) === 0"
                                        size="icon"
                                        variant="ghost"
                                        class="cursor-pointer"
                                        title="Hapus Regional"
                                        @click="handleDeleteRegional(regional)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                </template>
                                <template v-else>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="hover:bg-green-50"
                                        @click="handleUpdateRegional(regional)"
                                    >
                                        <Check class="h-4 w-4 text-green-600" />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="hover:bg-red-50"
                                        @click="cancelEdit"
                                    >
                                        <X class="h-4 w-4 text-red-600" />
                                    </Button>

                                </template>
                            </div>
                        </TableCell>
                    </TableRow>

                    <template v-if="expandedRegionals.includes(regional.id)">

                        <!-- ===== ROW: AREA (di bawah regional) ===== -->
                        <template v-for="area in regional.areas" :key="area.id">
                            <TableRow
                                class="cursor-pointer hover:bg-muted/30"
                                :class="{ 'bg-muted/10': expandedAreas.includes(area.id) }"
                                @click="toggleArea(area.id)"
                            >
                                <TableCell>
                                    <!-- indentasi level 1 -->
                                    <div class="flex items-center pl-5">
                                        <ChevronDown
                                            v-if="expandedAreas.includes(area.id)"
                                            class="h-4 w-4 text-gray-400"
                                        />
                                        <ChevronRight v-else class="h-4 w-4 text-gray-400" />
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span class="font-semibold text-sm">
                                        {{ area.name }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="outline" class="font-mono">
                                        {{ area.branches?.length ?? 0 }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right" @click.stop>
                                    <div class="flex justify-end gap-1">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            class="cursor-pointer"
                                            title="Edit Area"
                                            @click="handleFormArea(area)"
                                        >
                                            <Pencil class="h-4 w-4 text-blue-600" />
                                        </Button>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            class="cursor-pointer"
                                            title="Tambah Cabang"
                                            @click="handleFormBranch(null, regional.id, area.id)"
                                        >
                                            <Plus class="h-4 w-4 text-green-600" />
                                        </Button>
                                        <Button
                                            v-if="(area.branches?.length ?? 0) === 0"
                                            size="icon"
                                            variant="ghost"
                                            class="cursor-pointer"
                                            title="Hapus Area"
                                            @click="handleDeleteArea(area)"
                                        >
                                            <Trash2 class="h-4 w-4 text-red-600" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <!-- ===== ROW: BRANCH di bawah AREA ===== -->
                            <template v-if="expandedAreas.includes(area.id)">
                                <TableRow
                                    v-for="branch in area.branches"
                                    :key="branch.id"
                                >
                                    <TableCell></TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2 pl-6">
                                            <div class="h-1.5 w-1.5 rounded-full bg-slate-300"></div>
                                            <span class="text-sm">{{ branch.name }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell></TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-1">
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="cursor-pointer"
                                                title="Edit Cabang"
                                                @click="handleFormBranch(branch)"
                                            >
                                                <Pencil class="h-3.5 w-3.5 text-slate-500" />
                                            </Button>
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="cursor-pointer"
                                                title="Hapus Cabang"
                                                @click="handleDeleteBranch(branch)"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-600" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </template>
                        </template>

                        <!-- ===== ROW: BRANCH langsung di bawah REGIONAL (tanpa area) ===== -->
                        <TableRow
                            v-for="branch in regional.branches"
                            :key="branch.id"
                        >
                            <TableCell></TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2 pl-3">
                                    <div class="h-1.5 w-1.5 rounded-full bg-blue-300"></div>
                                    <span class="text-sm">{{ branch.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell></TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="cursor-pointer"
                                        title="Edit Cabang"
                                        @click="handleFormBranch(branch)"
                                    >
                                        <Pencil class="h-3.5 w-3.5 text-slate-500" />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="cursor-pointer"
                                        title="Hapus Cabang"
                                        @click="handleDeleteBranch(branch)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                    </template>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
