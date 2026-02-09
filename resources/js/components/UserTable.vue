<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';
import {
    EllipsisVertical,
    Settings2,
    Pencil,
    Trash2,
    Search,
    Plus,
} from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { computed, ref, watch } from 'vue';
import { User } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Popover,
    PopoverTrigger,
    PopoverContent,
} from '@/components/ui/popover';
import admin from '@/routes/admin';
import { router } from '@inertiajs/vue3';
import DialogDeleteUser from '@/components/DialogDeleteUser.vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    users: User[];
}>();

const dialogDelete = ref(false);
const deleteUser = ref<null | User>(null);

const searchQuery = ref('');

const defaultColumns = {
    name: true,
    email: true,
    position: true,
    role: true,
};

const getStoredColumns = () => {
    const stored = localStorage.getItem('usersTableColumns');
    return stored ? JSON.parse(stored) : defaultColumns;
};
const visibleColumns = ref(getStoredColumns());

watch(
    visibleColumns,
    (newValue) => {
        localStorage.setItem('usersTableColumns', JSON.stringify(newValue));
    },
    { deep: true },
);

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;

    const query = searchQuery.value.toLowerCase();
    return props.users.filter(
        (user) =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query),
    );
});

// Functions untuk actions
const handleEdit = (user: User) => {
    router.visit(admin.users.edit(user).url);
};

const handleDelete = (user: User) => {
    dialogDelete.value = true;
    deleteUser.value = user;
};

const resetColumns = () => {
    visibleColumns.value = { ...defaultColumns };
};

const handleCreateUser = () => {
    // Implement create user logic
    console.log('Create new user');
    router.visit(admin.users.create().url);
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header dengan Search dan Filter -->
        <div class="flex items-center justify-between gap-4">
            <div class="relative max-w-sm flex-1">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    placeholder="Cari pengguna"
                    class="pl-9"
                />
            </div>

            <div class="flex items-center gap-2">
                <!-- Column Filter -->
                <Popover>
                    <PopoverTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Settings2 class="mr-2 h-4 w-4" />
                            Kolom
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-56" align="end">
                        <div class="space-y-4">
                            <div>
                                <h4 class="mb-3 text-sm font-medium">
                                    Tampilkan kolom
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox
                                            id="col-name"
                                            v-model="visibleColumns.name"
                                        />
                                        <label
                                            for="col-name"
                                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            Nama
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox
                                            id="col-email"
                                            v-model="visibleColumns.email"
                                        />
                                        <label
                                            for="col-email"
                                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            Email
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox
                                            id="col-position"
                                            v-model="visibleColumns.position"
                                        />
                                        <label
                                            for="col-position"
                                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            Jabatan
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox
                                            id="col-role"
                                            v-model="visibleColumns.role"
                                        />
                                        <label
                                            for="col-role"
                                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            Role
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t pt-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="w-full"
                                    @click="resetColumns"
                                >
                                    Atur ulang
                                </Button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>

                <Button @click="handleCreateUser" size="sm">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Pengguna
                </Button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="font-bold" v-if="visibleColumns.name"
                            >Nama</TableHead
                        >
                        <TableHead class="font-bold" v-if="visibleColumns.email"
                            >Email</TableHead
                        >
                        <TableHead
                            class="font-bold"
                            v-if="visibleColumns.position"
                            >Jabatan
                        </TableHead>
                        <TableHead class="font-bold" v-if="visibleColumns.role"
                            >Role</TableHead
                        >
                        <TableHead class="w-[50px]"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="filteredUsers.length === 0">
                        <TableCell
                            :colspan="
                                Object.values(visibleColumns).filter(Boolean)
                                    .length + 1
                            "
                            class="text-center text-muted-foreground"
                        >
                            No users found
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="user in filteredUsers" :key="user.id">
                        <TableCell
                            v-if="visibleColumns.name"
                            class="font-medium"
                        >
                            {{ user.name }}
                        </TableCell>
                        <TableCell v-if="visibleColumns.email">
                            {{ user.email }}
                        </TableCell>
                        <TableCell v-if="visibleColumns.position">
                            {{ user.position }}
                        </TableCell>
                        <TableCell v-if="visibleColumns.role">
                            <Badge
                                variant="secondary"
                                :class="
                                    user.role === 'admin'
                                        ? 'bg-blue-500 text-white dark:bg-blue-600'
                                        : ''
                                "
                            >
                                {{ user.role }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0"
                                    >
                                        <span class="sr-only">Open menu</span>
                                        <EllipsisVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel
                                        >Aksi</DropdownMenuLabel
                                    >
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="handleEdit(user)">
                                        <Pencil class="mr-2 h-4 w-4" />
                                        Update
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="handleDelete(user)"
                                        class="text-destructive focus:text-destructive"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Results info -->
        <div class="text-sm text-muted-foreground">
            Menampilkan {{ filteredUsers.length }} dari {{ users.length }} pengguna
        </div>
    </div>

    <DialogDeleteUser
        v-model:is-open="dialogDelete"
        v-model:user-data="deleteUser"
    />
</template>

<style scoped></style>
