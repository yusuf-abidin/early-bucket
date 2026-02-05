<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, watch, computed } from 'vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan profil',
        href: edit().url,
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);

// Form untuk profile info (name & email)
const profileForm = useForm({
    name: user.value.name,
    email: user.value.email,
});

// Form terpisah untuk avatar
const avatarForm = useForm({
    avatar: null as File | null,
});

const avatarPreview = ref<string | null>(null);
const forceUpdateKey = ref(0); // Key untuk force re-render avatar

// Computed untuk existing avatar URL
const existingAvatarUrl = computed(() => {
    return user.value.avatar
        ? `/storage/${user.value.avatar}?v=${forceUpdateKey.value}`
        : null;
});

// Set preview awal ke existing avatar jika ada
avatarPreview.value = existingAvatarUrl.value;

// Watch perubahan user.avatar dari props (setelah reload data dari server)
watch(
    () => user.value.avatar,
    (newAvatar) => {
        if (newAvatar) {
            forceUpdateKey.value++;
            avatarPreview.value = `/storage/${newAvatar}?v=${forceUpdateKey.value}`;
        } else {
            avatarPreview.value = null;
        }
    },
);

// Watch perubahan file avatar untuk generate preview
watch(
    () => avatarForm.avatar,
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
);

const submitProfile = () => {
    const route = ProfileController.update.form();
    profileForm.submit(route.method, route.action);
};

const submitAvatar = () => {
    if (!avatarForm.avatar) return;

    const route = ProfileController.updateAvatar.form();
    avatarForm.submit(route.method, route.action, {
        preserveScroll: true,
        onError: () => {
            // Kembalikan preview ke existing avatar jika error
            avatarPreview.value = existingAvatarUrl.value;
            avatarForm.avatar = null;
            // Reset file input
            const fileInput = document.getElementById(
                'avatar',
            ) as HTMLInputElement;
            if (fileInput) fileInput.value = '';
        },
    });
};

const removeAvatar = () => {
    const route = ProfileController.deleteAvatar.form();
    avatarForm.submit(route.method, route.action, {
        preserveScroll: true,
        onSuccess: () => {
            avatarForm.avatar = null;
            avatarPreview.value = null;
        },
    });
};

const cancelAvatarUpload = () => {
    avatarForm.avatar = null;
    avatarPreview.value = existingAvatarUrl.value;
    // Reset file input
    const fileInput = document.getElementById('avatar') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Pengaturan profil" />

        <SettingsLayout>
            <!-- Avatar Upload Section -->
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Foto Profil"
                    description="Ganti atau hapus foto profil"
                />

                <div
                    class="flex flex-col items-center space-y-4 sm:flex-row sm:items-start sm:space-y-0 sm:space-x-6"
                >
                    <Avatar class="h-32 w-32 border-2 border-gray-200">
                        <AvatarImage
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            :key="avatarPreview"
                        />
                        <AvatarFallback
                            class="bg-gray-200 text-3xl text-gray-600"
                        >
                            {{ user.name.charAt(0).toUpperCase() }}
                        </AvatarFallback>
                    </Avatar>

                    <div class="flex flex-col space-y-3">
                        <div class="space-y-2">
                            <Label for="avatar" class="cursor-pointer">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    as="span"
                                    :disabled="avatarForm.processing"
                                >
                                    Pilih Gambar
                                </Button>
                            </Label>
                            <Input
                                id="avatar"
                                type="file"
                                accept="image/*"
                                @input="
                                    avatarForm.avatar = $event.target.files[0]
                                "
                                class="hidden"
                            />
                            <p class="text-xs text-muted-foreground">
                                PNG, JPG maksimal 1MB
                            </p>
                            <InputError :message="avatarForm.errors.avatar" />
                        </div>

                        <div class="flex gap-2">
                            <Button
                                v-if="avatarForm.avatar"
                                @click="submitAvatar"
                                :disabled="avatarForm.processing"
                                size="sm"
                            >
                                {{
                                    avatarForm.processing
                                        ? 'Uploading...'
                                        : 'Upload'
                                }}
                            </Button>

                            <Button
                                v-if="existingAvatarUrl && !avatarForm.avatar"
                                @click="removeAvatar"
                                :disabled="avatarForm.processing"
                                variant="destructive"
                                size="sm"
                            >
                                {{
                                    avatarForm.processing
                                        ? 'Menghapus...'
                                        : 'Hapus'
                                }}
                            </Button>

                            <Button
                                v-if="avatarForm.avatar"
                                @click="cancelAvatarUpload"
                                variant="ghost"
                                size="sm"
                                :disabled="avatarForm.processing"
                            >
                                Cancel
                            </Button>
                        </div>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="avatarForm.recentlySuccessful"
                                class="text-sm text-green-600"
                            >
                                Avatar updated successfully.
                            </p>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Informasi Profil"
                    description="Ubah nama dan alamat email"
                />

                <form @submit.prevent="submitProfile" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nama</Label>
                        <Input
                            id="name"
                            v-model="profileForm.name"
                            class="mt-1 block w-full"
                            required
                            autocomplete="name"
                            placeholder="Nama lengkap"
                            :disabled="profileForm.processing"
                        />
                        <InputError
                            class="mt-2"
                            :message="profileForm.errors.name"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Alamat email</Label>
                        <Input
                            id="email"
                            v-model="profileForm.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autocomplete="username"
                            placeholder="Alamat email"
                            :disabled="profileForm.processing"
                        />
                        <InputError
                            class="mt-2"
                            :message="profileForm.errors.email"
                        />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="profileForm.processing"
                            data-test="update-profile-button"
                        >
                            Simpan
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="profileForm.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Tersimpan.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
