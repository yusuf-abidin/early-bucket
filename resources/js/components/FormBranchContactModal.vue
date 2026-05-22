<script setup lang="ts">
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
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    BranchContact,
    EditBranchContactPayload,
    Regional,
} from '@/types';
import BranchContactController from '@/actions/App/Http/Controllers/BranchContactController';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import branchContact from '@/routes/branch-contact';
import DbmscContactController from "@/actions/App/Http/Controllers/DbmscContactController";
import dbmscContact from "@/routes/dbmsc-contact";

const formBranchContactIsOpen = defineModel<boolean>('form-contact-is-open', {
    default: false,
});

const selectedRegional = defineModel<Regional | null>('selected-regional', {
    default: null,
});

const selectedBranchContact = defineModel<BranchContact | null>('selected-branch-contact', {
    default: null,
});

const editBranchContactPayload = defineModel<EditBranchContactPayload | null>(
    'edit-payload',
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

    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreview.value);
    }

    form.avatar = file;
    form.remove_avatar = 0;
    avatarPreview.value = URL.createObjectURL(file);

    if (avatarInputRef.value) avatarInputRef.value.value = '';
};

const removeAvatar = () => {
    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreview.value);
    }
    avatarPreview.value = null;
    form.avatar = null;
    form.remove_avatar = 1;
    if (avatarInputRef.value) avatarInputRef.value.value = '';
};

const closeModal = () => {
    selectedBranchContact.value = null;
    formBranchContactIsOpen.value = false;
    editBranchContactPayload.value = null;

    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreview.value);
    }

    avatarPreview.value = null;
    form.reset();
    form.clearErrors();
};

const form = useForm({
    name: '',
    nip: '',
    phone: '',
    avatar: null as File | null,
    remove_avatar: 0,

    branch_name: '',
    regional_id: null as number | null,

    branch_contact_id: null as number | null,
});

const submit = () => {
    let route = null;
    if (editBranchContactPayload.value?.target_type == "BM") {
        route = !editBranchContactPayload.value?.branch_contact
        ? BranchContactController.store.form()
        : BranchContactController.update.form(editBranchContactPayload.value?.branch_contact.id);
    }else if(editBranchContactPayload.value?.target_type == "DBMSC") {
        route = !editBranchContactPayload.value?.dbmsc_contact
        ? DbmscContactController.store.form()
        : DbmscContactController.update.form(editBranchContactPayload.value?.dbmsc_contact.id);
    }

    form.submit(route!.method, route!.action, {
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

watch(
    () => editBranchContactPayload.value,
    (data) => {
        if (!data) {
            form.reset();
            return;
        }

        if (!data.dbmsc_contact || !data.branch_contact) {
            form.reset();
        }

        if (data.target_type == 'BM') {
            form.regional_id = selectedRegional.value?.id ?? null;
            form.branch_name = data.branch_contact?.branch_name ?? '';

            form.name = data.branch_contact?.name ?? '';
            form.nip = data.branch_contact?.nip ?? '';
            form.phone = data.branch_contact?.phone ?? '';
        } else if (data.target_type == 'DBMSC') {
            form.branch_contact_id = selectedBranchContact.value?.id ?? null;

            form.name = data.dbmsc_contact?.name ?? '';
            form.nip = data.dbmsc_contact?.nip ?? '';
            form.phone = data.dbmsc_contact?.phone ?? '';
        }

        if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
            URL.revokeObjectURL(avatarPreview.value);
        }

        const avatar = data.branch_contact ? data.branch_contact.avatar : data.dbmsc_contact?.avatar

        avatarPreview.value =
            (avatar && `/storage/${avatar}`) ?? null;
        form.remove_avatar = 0;
    },
    {
        immediate: true,
        deep: true,
    },
);

watch(
    () => selectedRegional.value,
    (data) => {
        if (!data) return;
        form.regional_id = data.id;
    },
);

const deleteContact = () => {
    let contact = null;
    let route = null;
    if (editBranchContactPayload.value?.target_type == "BM") {
        contact = editBranchContactPayload.value?.branch_contact;
        route = branchContact.destroy(contact!.id).url
    } else {
        contact = editBranchContactPayload.value?.dbmsc_contact;
        route = dbmscContact.destroy(contact!.id).url
    }
    if (!contact) return;


    router.delete(route, {
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
</script>

<template>
    <Dialog v-model:open="formBranchContactIsOpen" @update:open="closeModal">
        <DialogContent
            :aria-describedby="undefined"
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea>
                <DialogHeader class="px-6 pt-6">
                    <DialogTitle class="text-2xl font-semibold">
                        {{ editBranchContactPayload?.branch_contact || editBranchContactPayload?.dbmsc_contact ? 'Edit Kontak' : 'Buat Kontak' }}
                    </DialogTitle>
                    <DialogDescription v-if="editBranchContactPayload?.target_type == 'BM'" class="-mt-2">
                        {{ selectedRegional?.name }}
                    </DialogDescription>
                    <DialogDescription v-else class="-mt-2">
                        {{ selectedBranchContact?.branch_name }}
                    </DialogDescription>
                </DialogHeader>

                <form
                    @submit.prevent="submit"
                    class="space-y-6 px-6"
                    enctype="multipart/form-data"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>Foto Profil</Label>
                            <span
                                class="rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground"
                            >
                                Rasio 1:1 disarankan
                            </span>
                        </div>

                        <input
                            ref="avatarInputRef"
                            type="file"
                            name="avatar"
                            accept="image/*"
                            class="hidden"
                            :disabled="form.processing"
                            @change="onAvatarChange"
                        />

                        <input
                            type="hidden"
                            name="remove_avatar"
                            :value="form.remove_avatar"
                        />

                        <div class="flex items-center gap-4">
                            <button
                                type="button"
                                :disabled="form.processing"
                                @click="openAvatarPicker"
                                :class="[
                                    'relative h-20 w-20 shrink-0 overflow-hidden rounded-full border-2 transition-all focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none',
                                    form.errors.avatar
                                        ? 'border-destructive'
                                        : 'border-muted hover:border-primary',
                                    form.processing
                                        ? 'cursor-not-allowed opacity-60'
                                        : 'cursor-pointer',
                                ]"
                                title="Klik untuk pilih foto"
                            >
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    alt="Preview avatar"
                                    class="h-full w-full object-cover"
                                />

                                <span
                                    v-else
                                    class="flex h-full w-full items-center justify-center bg-muted text-muted-foreground"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-7 w-7"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"
                                        />
                                        <circle cx="12" cy="13" r="4" />
                                    </svg>
                                </span>

                                <span
                                    v-if="!form.processing"
                                    class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40 opacity-0 transition-opacity hover:opacity-100"
                                    aria-hidden="true"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-white"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"
                                        />
                                        <circle cx="12" cy="13" r="4" />
                                    </svg>
                                </span>
                            </button>

                            <div class="flex flex-col gap-1.5">
                                <p class="text-xs text-muted-foreground">
                                    Klik foto untuk mengubah.
                                </p>

                                <button
                                    v-if="avatarPreview"
                                    type="button"
                                    :disabled="form.processing"
                                    @click="removeAvatar"
                                    class="flex w-fit cursor-pointer items-center gap-1 text-xs text-destructive transition-colors hover:underline disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-3.5 w-3.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <polyline points="3 6 5 6 21 6" />
                                        <path
                                            d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                        />
                                        <path d="M10 11v6M14 11v6" />
                                        <path
                                            d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"
                                        />
                                    </svg>
                                    Hapus foto
                                </button>

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
                        <template v-if="editBranchContactPayload?.target_type == 'BM'">
                            <Label for="branch_name">
                                Nama Cabang
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                :disabled="form.processing"
                                v-model="form.branch_name"
                                id="branch_name"
                                type="text"
                                name="branch_name"
                            />
                            <p
                                v-if="form.errors.branch_name"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.branch_name }}
                            </p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <Label for="name">Nama</Label>
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
                        <Label for="nip">NIP</Label>
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
                        <Label for="phone">Kontak</Label>
                        <Input
                            :disabled="form.processing"
                            v-model="form.phone"
                            id="phone"
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
                    </div>
                </form>

                <DialogFooter
                    class="flex flex-col-reverse gap-3 px-6 pt-4 pb-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <Button
                        v-if="editBranchContactPayload?.dbmsc_contact || editBranchContactPayload?.branch_contact"
                        variant="destructive"
                        :disabled="form.processing"
                        class="w-full gap-1.5 sm:w-auto"
                        @click="deleteContact"
                    >
                        Hapus Kontak
                    </Button>

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
