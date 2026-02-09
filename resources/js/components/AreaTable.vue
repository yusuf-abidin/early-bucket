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
import { Area, Branch } from '@/types';
import { router } from '@inertiajs/vue3';
import admin from '@/routes/admin';

// Props definition
const props = defineProps<{
    areas: Area[];
    search?: string;
}>();

const selectedArea = defineModel<Area | null>('selectedArea', {
    default: null,
});
const selectedBranch = defineModel<Branch | null>('selectedBranch', {
    default: null,
});
const formAreaIsOpen = defineModel<boolean>('formAreaIsOpen', {
    default: false,
});
const formBranchIsOpen = defineModel<boolean>('formBranchIsOpen', {
    default: false,
});
const dialogDeleteArea = defineModel<boolean>('dialogDeleteArea', {
    default: false,
});
const dialogDeleteBranch = defineModel<boolean>('dialogDeleteBranch', {
    default: false,
});

const handleFormArea = (area: Area | null = null) => {
    formAreaIsOpen.value = true;
    selectedArea.value = area;
};

const handleFormBranch = (branch: Branch | null = null) => {
    formBranchIsOpen.value = true;
    selectedBranch.value = branch;
};

const handleDeleteBranch = (branch: Branch) => {
    selectedBranch.value = branch;
    dialogDeleteBranch.value = true;
};

const handleDeleteArea = (area: Area) => {
    selectedArea.value = area;
    dialogDeleteArea.value = true;
};

// States
const expandedAreas = ref<number[]>([]);
const editingAreaId = ref<number | null>(null);
const editName = ref(''); // State sementara untuk nama yang sedang diedit
const searchQuery = ref(props.search || '');
const areaInputRefs = ref<Record<number, any>>({});

// Computed
const allExpanded = computed(() => {
    return expandedAreas.value.length === props.areas.length;
});

const applyFilters = () => {
    const query: Record<string, any> = {
        search: searchQuery.value || undefined,
    };
    router.get(window.location.pathname, query, {
        preserveState: true,
        replace: true,
    });
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
    if (allExpanded.value) {
        expandedAreas.value = [];
    } else {
        expandedAreas.value = props.areas.map((area) => area.id);
    }
};

const startEditArea = async (area: Area) => {
    editingAreaId.value = area.id;
    editName.value = area.name; // Copy nama ke state sementara

    await nextTick();

    const component = areaInputRefs.value[area.id];
    if (component) {
        const inputElement =
            component.$el instanceof HTMLInputElement
                ? component.$el
                : component.$el?.querySelector('input');
        if (inputElement) {
            inputElement.focus();
        }
    }
};

const cancelEdit = () => {
    editingAreaId.value = null;
    editName.value = '';
};

const handleUpdateArea = (area: Area) => {
    if (!editName.value.trim()) return;

    router.patch(
        admin.areas.update(area.id).url,
        {
            name: editName.value,
        },
        {
            onSuccess: () => {
                editingAreaId.value = null;
            },
        },
    );
};
</script>

<template>
    <div
        class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex w-full items-center gap-2 sm:max-w-sm">
            <div class="relative flex-1">
                <Search
                    class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground"
                />
                <Input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari area atau cabang..."
                    class="w-full pl-9"
                    @keyup.enter="applyFilters"
                />
            </div>

            <Button
                variant="secondary"
                size="sm"
                @click="applyFilters"
                class="cursor-pointer"
            >
                Cari
            </Button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Button
                variant="outline"
                @click="toggleExpandAll"
                class="flex-1 cursor-pointer sm:flex-none"
            >
                {{ allExpanded ? 'Collapse All' : 'Expand All' }}
            </Button>

            <Button
                variant="secondary"
                @click="handleFormBranch()"
                class="flex-1 cursor-pointer gap-2 sm:flex-none"
            >
                <Plus class="h-4 w-4" />
                <span class="whitespace-nowrap">Tambah Cabang</span>
            </Button>

            <Button
                @click="handleFormArea()"
                class="flex-1 cursor-pointer gap-2 sm:flex-none"
            >
                <Plus class="h-4 w-4" />
                <span class="whitespace-nowrap">Tambah Area</span>
            </Button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-md border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[50px]"></TableHead>
                    <TableHead class="font-bold">Nama Area/Cabang</TableHead>
                    <TableHead class="w-[150px] text-center font-bold"
                        >Total Cabang</TableHead
                    >
                    <TableHead class="w-[150px] text-right font-bold"
                        >Aksi</TableHead
                    >
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-for="area in areas" :key="area.id">
                    <TableRow
                        class="cursor-pointer hover:bg-muted/50"
                        :class="{
                            'bg-muted/30': expandedAreas.includes(area.id),
                        }"
                        @click="toggleArea(area.id)"
                    >
                        <TableCell class="p">
                            <ChevronRight
                                v-if="!expandedAreas.includes(area.id)"
                                class="h-4 w-4 text-gray-500"
                            />
                            <ChevronDown v-else class="h-4 w-4 text-gray-500" />
                        </TableCell>
                        <TableCell>
                            <div @click.stop class="max-w-md">
                                <div
                                    v-if="editingAreaId === area.id"
                                    class="flex items-center gap-2"
                                >
                                    <Input
                                        v-model="editName"
                                        :ref="
                                            (el) =>
                                                (areaInputRefs[area.id] = el)
                                        "
                                        @keyup.enter="handleUpdateArea(area)"
                                        @keyup.esc="cancelEdit"
                                        class="h-8"
                                    />
                                </div>
                                <span v-else class="font-semibold">
                                    {{ area.name }}
                                </span>
                            </div>
                        </TableCell>
                        <TableCell class="text-center">
                            <Badge variant="secondary" class="font-mono">
                                {{ area.branches?.length || 0 }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right" @click.stop>
                            <div class="flex justify-end gap-1">
                                <template v-if="editingAreaId !== area.id">
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        @click="startEditArea(area)"
                                        title="Edit Area"
                                        class="cursor-pointer"
                                    >
                                        <Pencil class="h-4 w-4 text-blue-600" />
                                    </Button>
                                    <Button
                                        class="cursor-pointer"
                                        v-if="area.branches?.length === 0"
                                        size="icon"
                                        variant="ghost"
                                        @click="handleDeleteArea(area)"
                                        title="Hapus Area"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                </template>

                                <template v-else>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        @click="handleUpdateArea(area)"
                                        class="hover:bg-green-50"
                                    >
                                        <Check class="h-4 w-4 text-green-600" />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        @click="cancelEdit"
                                        class="hover:bg-red-50"
                                    >
                                        <X class="h-4 w-4 text-red-600" />
                                    </Button>
                                </template>
                            </div>
                        </TableCell>
                    </TableRow>

                    <template v-if="expandedAreas.includes(area.id)">
                        <TableRow
                            v-for="branch in area.branches"
                            :key="branch.id"
                        >
                            <TableCell></TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2 pl-8">
                                    <div
                                        class="h-2 w-2 rounded-full bg-slate-300"
                                    ></div>
                                    <span>{{ branch.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell></TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        class="cursor-pointer"
                                        size="icon"
                                        variant="ghost"
                                        @click="handleFormBranch(branch)"
                                        title="Edit Cabang"
                                    >
                                        <Pencil
                                            class="h-3.5 w-3.5 text-slate-500"
                                        />
                                    </Button>
                                    <Button
                                        class="cursor-pointer"
                                        size="icon"
                                        variant="ghost"
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

                <TableRow v-if="areas.length === 0">
                    <TableCell
                        colspan="4"
                        class="py-10 text-center text-muted-foreground"
                    >
                        Data area tidak ditemukan.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
