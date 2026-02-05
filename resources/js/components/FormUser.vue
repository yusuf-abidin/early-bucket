<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import AdminController from '@/actions/App/Http/Controllers/AdminController';
import admin from '@/routes/admin';
import type { User, Color } from '@/types';

interface Props {
    user?: User;
    colors: Color[];
    mode?: 'create' | 'edit';
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
});

const isEditMode = computed(() => props.mode === 'edit' || !!props.user);

const form = useForm({
    name: props.user?.name || '',
    position: props.user?.position || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    role: props.user?.role || '',
    color_id: props.user?.color_id || null,
    avatar: null as File | null,
});

const avatarPreview = ref<string | null>(null);
const openColorDropdown = ref(false);

// Computed untuk existing avatar URL
const existingAvatarUrl = computed(() => {
    return props.user?.avatar ? `/storage/${props.user.avatar}` : null;
});

// Set preview awal ke existing avatar jika ada (edit mode)
if (isEditMode.value && existingAvatarUrl.value) {
    avatarPreview.value = existingAvatarUrl.value;
}

// Watch perubahan file avatar untuk generate preview
watch(
    () => form.avatar,
    (newFile) => {
        if (newFile instanceof File) {
            const reader = new FileReader();
            reader.onload = (e) => {
                avatarPreview.value = e.target?.result as string;
            };
            reader.readAsDataURL(newFile);
        } else if (!newFile && existingAvatarUrl.value) {
            avatarPreview.value = existingAvatarUrl.value;
        } else {
            avatarPreview.value = null;
        }
    },
    { immediate: true },
);

// Get color by id
const getColorById = (colorId?: number | null) => {
    if (!colorId) return null;
    return props.colors.find((c) => c.id === colorId);
};

const selectedColor = computed(() => getColorById(form.color_id));

// Toggle color dropdown
const toggleColorDropdown = () => {
    openColorDropdown.value = !openColorDropdown.value;
};

// Select color
const selectColor = (colorId: number) => {
    form.color_id = colorId;
    openColorDropdown.value = false;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    openColorDropdown.value = false;
};

const submit = () => {
    if (isEditMode.value && props.user) {
        const route = AdminController.update.form(props.user.id);
        form.submit(route.method, route.action, {
            onFinish: () => {
                form.reset('password', 'password_confirmation', 'avatar');
                avatarPreview.value = existingAvatarUrl.value;
            },
        });
    } else {
        const route = AdminController.store.form();
        form.submit(route.method, route.action, {
            onFinish: () => {
                form.reset('password', 'password_confirmation');
                form.avatar = null;
                avatarPreview.value = null;
            },
        });
    }
};

const handleCancel = () => {
    if (isEditMode.value) {
        router.visit(admin.users.index().url);
    } else {
        form.reset();
        avatarPreview.value = null;
        form.avatar = null;
    }
};

const cardTitle = computed(() =>
    isEditMode.value ? 'Edit Akun Pengguna' : 'Buat Akun Pengguna',
);

const cardDescription = computed(() =>
    isEditMode.value
        ? 'Perbarui informasi pengguna. Biarkan kolom password dan foto profil kosong jika Anda tidak ingin mengubahnya.'
        : 'Tambahkan pengguna baru ke sistem. Semua kolom wajib diisi kecuali foto profil.',
);

const submitButtonText = computed(() =>
    form.processing
        ? isEditMode.value
            ? 'Updating...'
            : 'Membuat...'
        : isEditMode.value
          ? 'Update User'
          : 'Buat User',
);

const cancelButtonText = computed(() => (isEditMode.value ? 'Batal' : 'Reset'));
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8 dark:bg-black"
        @click="closeDropdown"
    >
        <Card class="w-full max-w-lg shadow-lg">
            <form @submit.prevent="submit" enctype="multipart/form-data">
                <CardHeader class="space-y-1">
                    <CardTitle class="text-2xl font-bold">{{
                        cardTitle
                    }}</CardTitle>
                    <CardDescription>
                        {{ cardDescription }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- Avatar Upload + Preview -->
                    <div class="flex flex-col items-center space-y-4">
                        <Avatar
                            class="h-32 w-32 border-4 border-dashed border-gray-300"
                        >
                            <AvatarImage
                                v-if="avatarPreview"
                                :src="avatarPreview"
                            />
                            <AvatarFallback class="bg-gray-200 text-3xl">
                                {{ form.name.charAt(0).toUpperCase() || 'U' }}
                            </AvatarFallback>
                        </Avatar>

                        <div class="space-y-2 text-center">
                            <Label for="avatar" class="cursor-pointer">
                                <span
                                    class="text-sm text-blue-600 hover:underline"
                                >
                                    {{
                                        isEditMode
                                            ? 'Unggah Foto Profil Baru (opsional)'
                                            : 'Unggah Foto Profil (opsional)'
                                    }}
                                </span>
                            </Label>
                            <Input
                                id="avatar"
                                type="file"
                                accept="image/*"
                                @input="form.avatar = $event.target.files[0]"
                                class="hidden"
                            />
                            <p class="text-xs text-muted-foreground">
                                PNG, JPG maksimal 1MB
                            </p>
                        </div>
                        <p
                            v-if="form.errors.avatar"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.avatar }}
                        </p>
                    </div>

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <Label
                            for="name"
                            :class="{ 'text-destructive': form.errors.name }"
                        >
                            Nama <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan nama lengkap"
                            :disabled="form.processing"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Position -->
                    <div class="space-y-1.5">
                        <Label
                            for="position"
                            :class="{
                                'text-destructive': form.errors.position,
                            }"
                        >
                            Jabatan <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="position"
                            v-model="form.position"
                            type="text"
                            placeholder="Masukkan nama jabatan"
                            :disabled="form.processing"
                        />
                        <p
                            v-if="form.errors.position"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.position }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <Label
                            for="email"
                            :class="{ 'text-destructive': form.errors.email }"
                        >
                            Email <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="user@example.com"
                            :disabled="form.processing"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <Label
                            for="password"
                            :class="{
                                'text-destructive': form.errors.password,
                            }"
                        >
                            {{ isEditMode ? 'Password Baru' : 'Password' }}
                            <span v-if="!isEditMode" class="text-destructive"
                                >*</span
                            >
                            <span v-else class="text-gray-500">(opsional)</span>
                        </Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            :placeholder="
                                isEditMode
                                    ? 'Biarkan kosong untuk mempertahankan password saat ini.'
                                    : 'Masukkan password yang aman'
                            "
                            :disabled="form.processing"
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5">
                        <Label
                            for="password_confirmation"
                            :class="{
                                'text-destructive':
                                    form.errors.password_confirmation,
                            }"
                        >
                            {{
                                isEditMode
                                    ? 'Konfirmasi Password Baru'
                                    : 'Konfirmasi Password'
                            }}
                            <span v-if="!isEditMode" class="text-destructive"
                                >*</span
                            >
                        </Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            :placeholder="
                                isEditMode
                                    ? 'Konfirmasi Password Baru'
                                    : 'Konfirmasi Password'
                            "
                            :disabled="form.processing"
                        />
                        <p
                            v-if="form.errors.password_confirmation"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Role -->
                    <div class="space-y-1.5">
                        <Label
                            for="role"
                            :class="{ 'text-destructive': form.errors.role }"
                        >
                            Role <span class="text-destructive">*</span>
                        </Label>
                        <Select v-model="form.role" :disabled="form.processing">
                            <SelectTrigger id="role">
                                <SelectValue placeholder="Pilih role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="user">User</SelectItem>
                                <SelectItem value="admin">Admin</SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.role"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.role }}
                        </p>
                    </div>

                    <!-- Color Selector -->
                    <div class="space-y-1.5">
                        <Label for="color">
                            Warna
                            <span class="text-gray-500">(opsional)</span>
                        </Label>
                        <div class="relative">
                            <button
                                @click.stop="toggleColorDropdown"
                                type="button"
                                class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                                :class="
                                    selectedColor
                                        ? selectedColor.class
                                        : 'bg-gray-50 text-gray-700'
                                "
                                :disabled="form.processing"
                            >
                                <span class="flex items-center gap-2">
                                    <div
                                        v-if="selectedColor"
                                        class="h-4 w-4 rounded"
                                        :class="selectedColor.class"
                                    ></div>
                                    <span class="capitalize">
                                        {{
                                            selectedColor
                                                ? selectedColor.name
                                                : 'Pilih warna'
                                        }}
                                    </span>
                                </span>
                                <ChevronDown class="h-4 w-4 opacity-50" />
                            </button>

                            <!-- Color Dropdown -->
                            <div
                                v-if="openColorDropdown"
                                class="absolute right-0 left-0 z-50 mt-2 rounded-md border border-gray-200 bg-white shadow-lg"
                                @click.stop
                            >
                                <div class="p-2">
                                    <div
                                        class="mb-2 px-2 text-xs font-medium text-gray-500"
                                    >
                                        Pilih warna
                                    </div>
                                    <div class="max-h-60 overflow-y-auto">
                                        <button
                                            v-for="color in colors"
                                            :key="color.id"
                                            type="button"
                                            @click="selectColor(color.id)"
                                            class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors hover:bg-gray-100"
                                            :class="
                                                form.color_id === color.id
                                                    ? 'bg-gray-50'
                                                    : ''
                                            "
                                        >
                                            <div
                                                class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded"
                                                :class="color.class"
                                            >
                                                <div
                                                    v-if="
                                                        form.color_id ===
                                                        color.id
                                                    "
                                                    class="h-2 w-2 rounded-full bg-current"
                                                ></div>
                                            </div>
                                            <span
                                                class="text-gray-700 capitalize"
                                                >{{ color.name }}</span
                                            >
                                        </button>

                                        <!-- Clear Selection Option -->
                                        <button
                                            v-if="form.color_id"
                                            type="button"
                                            @click="selectColor(null)"
                                            class="mt-1 flex w-full items-center gap-3 rounded-md border-t px-3 py-2 text-sm text-gray-500 transition-colors hover:bg-gray-100"
                                        >
                                            <div
                                                class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded border-2 border-dashed border-gray-300"
                                            ></div>
                                            <span>No Color</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p
                            v-if="form.errors.color_id"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.color_id }}
                        </p>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-between pt-4">
                    <Button
                        variant="outline"
                        type="button"
                        @click="handleCancel"
                        :disabled="form.processing"
                    >
                        {{ cancelButtonText }}
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ submitButtonText }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>

<style scoped></style>
