<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Branch, StcTlContact } from '@/types';
import { watch } from 'vue';
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
import stcTlContactController from '@/actions/App/Http/Controllers/StcTlContactController';
import stcTlContact from '@/routes/stc-tl-contact';

const formStcTlIsOpen = defineModel<boolean>('form-stc-tl-is-open', {
    default: false,
});

const editStcTlContact = defineModel<{
    branch: Branch;
    role: 'STC' | 'TL';
    contact: StcTlContact | null;
} | null>('edit-stc-tl-contact', {
    default: null,
});

const submit = () => {
    const contact = editStcTlContact.value?.contact;

    const route = !contact
        ? stcTlContactController.store.form()
        : stcTlContactController.update.form(contact.id);

    form.submit(route.method, route.action, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const form = useForm({
    branch_id: null as number | null,
    name: '',
    nip: '',
    phone: '',
    role: '',
});

const closeModal = () => {
    formStcTlIsOpen.value = false;
    form.reset();
    form.clearErrors();
};

const deleteContact = () => {
    if (!editStcTlContact.value?.contact) return;
    router.delete(stcTlContact.destroy(editStcTlContact.value.contact.id).url, {
        preserveScroll: true,
        onSuccess: closeModal,
    });
};

watch(
    () => editStcTlContact.value,
    (data) => {
        if (!data) return;

        form.name = data.contact?.name ?? '';
        form.nip = data.contact?.nip ?? '';
        form.phone = data.contact?.phone ?? '';
        form.branch_id = data.branch.id;
        form.role = data.role;
    },
);
</script>

<template>
    <Dialog v-model:open="formStcTlIsOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea>
                <DialogHeader class="px-6 pt-6">
                    <DialogTitle class="text-2xl font-semibold">
                        <template v-if="editStcTlContact?.contact">
                            Edit Kontak
                        </template>
                        <template v-else>
                            Tambah Kontak
                        </template>
                    </DialogTitle>
                    <DialogDescription>
                        <template v-if="editStcTlContact?.contact">
                            Perbarui informasi kontak
                            {{ editStcTlContact?.role }}
                            {{ editStcTlContact?.branch.name }}
                        </template>
                        <template v-else>
                            Tambah Kontak baru untuk
                            {{ editStcTlContact?.role }}
                            {{ editStcTlContact?.branch.name }}
                        </template>
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-6 px-6">
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
                    <Button
                        v-if="editStcTlContact?.contact"
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
