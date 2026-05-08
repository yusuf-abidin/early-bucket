<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { computed, watch, ref } from 'vue';
import { EditContactPayload } from '@/types';
import ContactClusterController from '@/actions/App/Http/Controllers/ContactClusterController';
import contactCluster from '@/routes/contact-cluster';

const formContactIsOpen = defineModel<boolean>('formContactIsOpen', {
    default: false,
});

const editContactPayload = defineModel<EditContactPayload | null>(
    'editContactPayload',
    {
        default: null,
    },
);

const avatarInputRef = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);

const openAvatarPicker = () => {
    if (form.processing) return;
    avatarInputRef.value?.click();
};

const onAvatarChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    // Revoke previous object URL to free memory
    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreview.value);
    }

    form.avatar = file;
    form.remove_avatar = 0; // user is uploading — don't remove
    avatarPreview.value = URL.createObjectURL(file);

    // Clear file input so the same file can be re-selected if needed
    if (avatarInputRef.value) avatarInputRef.value.value = '';
};

const removeAvatar = () => {
    // Revoke object URL if it was a blob
    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreview.value);
    }
    avatarPreview.value = null;
    form.avatar = null;
    form.remove_avatar = 1;
    if (avatarInputRef.value) avatarInputRef.value.value = '';
};
// ─────────────────────────────────────────────────────────────────────────────

const closeModal = () => {
    formContactIsOpen.value = false;
    // Revoke any lingering blob URL
    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreview.value);
    }
    avatarPreview.value = null;
    form.reset();
    form.clearErrors();
};

const getLocationLabel = computed(() => {
    if (editContactPayload.value?.targetType === 'REGIONAL') {
        return editContactPayload.value?.regional?.name ?? '';
    }
    if (editContactPayload.value?.targetType === 'AREA') {
        return editContactPayload.value?.area?.name ?? '';
    }
    return editContactPayload.value?.branch?.name ?? '';
});

const form = useForm({
    regional_id: '',
    name: '',
    nip: '',
    phone: '',
    area_id: '',
    branch_id: '',
    avatar: null as File | null,
    remove_avatar: 0,
});

const submit = () => {
    form.regional_id =
        editContactPayload.value?.targetType === 'REGIONAL'
            ? editContactPayload.value?.regional?.id
            : null;

    form.area_id =
        editContactPayload.value?.targetType === 'AREA'
            ? editContactPayload.value?.area?.id
            : null;
    form.branch_id =
        editContactPayload.value?.targetType === 'BRANCH'
            ? editContactPayload.value?.branch?.id
            : null;

    const contact = editContactPayload.value?.contact;

    const route = !contact
        ? ContactClusterController.store.form()
        : ContactClusterController.update.form(contact.id);

    form.submit(route.method, route.action, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            if (form.errors.avatar) {
                if (
                    avatarPreview.value &&
                    avatarPreview.value.startsWith('blob:')
                ) {
                    URL.revokeObjectURL(avatarPreview.value);
                }
                avatarPreview.value = null;
                form.avatar = null;
                if (avatarInputRef.value) avatarInputRef.value.value = '';
            }
        },
    });
};

const deleteContact = () => {
    router.delete(
        contactCluster.destroy(editContactPayload.value!.contact!.id).url,
        {
            preserveScroll: true,
            onSuccess: closeModal,
        },
    );
};

watch(
    () => editContactPayload.value,
    (data) => {
        if (!data) return;

        form.name = data.contact?.name ?? '';
        form.nip = data.contact?.nip ?? '';
        form.phone = data.contact?.phone ?? '';

        // Seed existing avatar as preview (existing URL, not blob)
        if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
            URL.revokeObjectURL(avatarPreview.value);
        }
        avatarPreview.value =
            (data.contact?.avatar && `/storage/${data.contact.avatar}`) ?? null;
        form.remove_avatar = 0;
    },
    { immediate: true },
);
</script>

<template>
    <Dialog v-model:open="formContactIsOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea>
                <DialogHeader class="px-6 pt-6">
                    <DialogTitle class="text-2xl font-semibold">
                        Edit Kontak
                    </DialogTitle>
                    <DialogDescription>
                        Perbarui informasi kontak {{ getLocationLabel }}.
                    </DialogDescription>
                </DialogHeader>

                <form
                    @submit.prevent="submit"
                    class="space-y-6 px-6"
                    enctype="multipart/form-data"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>
                                Foto Profil
                            </Label>
                            <span class="text-[10px] text-muted-foreground bg-muted px-1.5 py-0.5 rounded">
                                Rasio 1:1 disarankan
                            </span>
                        </div>
                        <!-- Hidden file input -->
                        <input
                            ref="avatarInputRef"
                            type="file"
                            name="avatar"
                            accept="image/*"
                            class="hidden"
                            :disabled="form.processing"
                            @change="onAvatarChange"
                        />

                        <!-- Hidden remove_avatar flag -->
                        <input
                            type="hidden"
                            name="remove_avatar"
                            :value="form.remove_avatar"
                        />

                        <div class="flex items-center gap-4">

                            <!-- Avatar circle -->
                            <button
                                type="button"
                                :disabled="form.processing"
                                @click="openAvatarPicker"
                                :class="[
                                    'relative h-20 w-20 shrink-0 overflow-hidden rounded-full border-2 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                                    form.errors.avatar
                                        ? 'border-destructive'
                                        : 'border-muted hover:border-primary',
                                    form.processing ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
                                ]"
                                title="Klik untuk pilih foto"
                            >
                                <!-- Preview -->
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    alt="Preview avatar"
                                    class="h-full w-full object-cover"
                                />

                                <!-- Empty state: camera icon -->
                                <span
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-muted text-muted-foreground"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                        <circle cx="12" cy="13" r="4"/>
                                    </svg>
                                </span>

                                <!-- Hover overlay -->
                                <span
                                    v-if="!form.processing"
                                    class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40 opacity-0 transition-opacity hover:opacity-100"
                                    aria-hidden="true"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                        <circle cx="12" cy="13" r="4"/>
                                    </svg>
                                </span>
                            </button>

                            <!-- Right: hint + hapus + error -->
                            <div class="flex flex-col gap-1.5">
                                <p class="text-xs text-muted-foreground">
                                    Klik foto untuk mengubah.
                                </p>

                                <!-- Tombol Hapus Avatar -->
                                <button
                                    v-if="avatarPreview"
                                    type="button"
                                    :disabled="form.processing"
                                    @click="removeAvatar"
                                    class="flex w-fit cursor-pointer items-center gap-1 text-xs text-destructive transition-colors hover:underline disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    Hapus foto
                                </button>

                                <!-- Error avatar -->
                                <p
                                    v-if="form.errors.avatar"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.avatar }}
                                </p>
                            </div>

                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="name">
                            Nama
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            :disabled="form.processing"
                            v-model="form.name"
                            id="name"
                            type="text"
                            name="name"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="nip"> NIP </Label>
                        <Input
                            :disabled="form.processing"
                            v-model="form.nip"
                            id="nip"
                            type="text"
                            name="nip"
                        />
                        <p
                            v-if="form.errors.nip"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.nip }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="phone"> Kontak </Label>
                        <Input
                            :disabled="form.processing"
                            v-model="form.phone"
                            id="phone"
                            name="phone"
                            type="tel"
                            placeholder="628xxx"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <p
                            v-if="form.errors.regional_id"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.regional_id }}
                        </p>
                        <p
                            v-if="form.errors.area_id"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.area_id }}
                        </p>
                        <p
                            v-if="form.errors.branch_id"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.branch_id }}
                        </p>
                    </div>
                </form>

                <DialogFooter
                    class="flex flex-col-reverse gap-3 px-6 pt-4 pb-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <!-- LEFT (Delete) -->
                    <Button
                        v-if="editContactPayload?.contact"
                        variant="destructive"
                        :disabled="form.processing"
                        class="w-full gap-1.5 sm:w-auto"
                        @click="deleteContact"
                    >
                        Hapus Kontak
                    </Button>

                    <!-- RIGHT GROUP -->
                    <div class="flex flex-col gap-2 sm:ml-auto sm:flex-row">
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="outline"
                                @click="closeModal"
                                :disabled="form.processing"
                                class="w-full sm:w-auto"
                            >
                                Batal
                            </Button>
                        </DialogClose>

                        <Button
                            type="submit"
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full min-w-24 gap-1.5 sm:w-auto"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </div>
                </DialogFooter>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
