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
import { computed, watch } from 'vue';
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
const closeModal = () => {
    formContactIsOpen.value = false;
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
                    class="flex w-full items-center justify-between! px-6 pt-4 pb-6"
                >
                    <!-- kiri -->
                    <Button
                        v-if="editContactPayload?.contact"
                        variant="destructive"
                        :disabled="form.processing"
                        class="gap-1.5"
                        @click="deleteContact"
                    >
                        Hapus Kontak
                    </Button>

                    <!-- kanan -->
                    <div class="ml-auto flex items-center gap-2">
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="outline"
                                @click="closeModal"
                                :disabled="form.processing"
                            >
                                Batal
                            </Button>
                        </DialogClose>

                        <Button
                            type="submit"
                            @click="submit"
                            :disabled="form.processing"
                            class="min-w-24 gap-1.5"
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
