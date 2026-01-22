<script setup lang="ts">
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import AdminController from '@/actions/App/Http/Controllers/AdminController';
import { router, useForm } from '@inertiajs/vue3';
import { defineProps, ref, watch, computed } from 'vue';
import type { Color, User } from '@/types';
import admin from '@/routes/admin'; // Asumsi User type punya 'avatar': string | null

const props = defineProps<{
    user: User;
    colors: Color[];
}>();

const form = useForm({
    name: props.user.name,
    position: props.user.position,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    avatar: null as File | null,
});

const avatarPreview = ref<string | null>(null);

// Computed untuk existing avatar URL
const existingAvatarUrl = computed(() => {
    return props.user.avatar ? `/storage/${props.user.avatar}` : null;
});

// Set preview awal ke existing avatar jika ada
avatarPreview.value = existingAvatarUrl.value;

// Watch perubahan file avatar untuk generate preview dari file baru
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

const submit = () => {
    const route = AdminController.update.form(props.user.id);
    form.submit(route.method, route.action, {
        onFinish: () => {
            form.reset('password', 'password_confirmation', 'avatar');
            avatarPreview.value = existingAvatarUrl.value; // Kembali ke existing jika gagal, tapi idealnya refresh page
        },
    });
};
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8 dark:bg-black"
    >
        <Card class="w-full max-w-lg shadow-lg">
            <form @submit.prevent="submit" enctype="multipart/form-data">
                <CardHeader class="space-y-1">
                    <CardTitle class="text-2xl font-bold"
                        >Edit User Account</CardTitle
                    >
                    <CardDescription>
                        Update user information. Leave password and avatar blank
                        if you don't want to change them.
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
                                    Upload New Avatar (optional)
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
                                PNG, JPG up to 1MB recommended
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
                            Name <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Enter full name"
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
                            Position <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="position"
                            v-model="form.position"
                            type="text"
                            placeholder="Enter position name"
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
                        <Label for="password">
                            New Password
                            <span class="text-gray-500">(optional)</span>
                        </Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Leave blank to keep current password"
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
                        <Label for="password_confirmation">
                            Confirm New Password
                        </Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Confirm new password"
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
                                <SelectValue placeholder="Select a role" />
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
                </CardContent>

                <CardFooter class="flex justify-between pt-4">
                    <Button
                        variant="outline"
                        type="button"
                        @click="router.visit(admin.users.index().url)"
                        :disabled="form.processing"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update User</span>
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>

<style scoped>
/* Optional: tambahan styling jika diperlukan */
</style>
