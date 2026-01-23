<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Area, Branch } from '@/types';
import BranchController from '@/actions/App/Http/Controllers/BranchController';
import { watch } from 'vue';
import {
    Dialog, DialogClose,
    DialogContent,
    DialogDescription, DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    areas: Area[];
}>();

const formBranchIsOpen = defineModel<boolean>('formBranchIsOpen', {
    default: false,
});

const selectedBranch = defineModel<Branch | null>('selectedBranch', {
    default: null,
});

const closeModal = () => {
    formBranchIsOpen.value = false;
    selectedBranch.value = null;
    form.reset();
    form.clearErrors();
};

const form = useForm({
    name: '',
    area_id: '' as string | number,
});

const submit = () => {
    const options = {
        onSuccess: () => {
            closeModal();
        },
        onFinish: () => {
            if (selectedBranch.value) closeModal();
        },
    };

    if (!selectedBranch.value) {
        const route = BranchController.store.form();
        form.submit(route.method, route.action, options);
    } else {
        const route = BranchController.update.form(selectedBranch.value.id);
        form.submit(route.method, route.action, options);
    }
};

watch(
    () => selectedBranch.value,
    (newBranch) => {
        if (newBranch) {
            const data = {
                name: newBranch.name ?? '',
                area_id: newBranch.area_id ?? '',
            };
            form.defaults(data);
            form.reset();
        } else {
            form.defaults({
                name: '',
                area_id: '',
            });
            form.reset();
        }
    },
);
</script>

<template>
    <Dialog v-model:open="formBranchIsOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea>
                <DialogHeader class="px-6 pt-6">
                    <DialogTitle class="text-2xl font-semibold">
                        {{
                            selectedBranch
                                ? 'Edit Cabang'
                                : 'Tambah Cabang Baru'
                        }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            selectedBranch
                                ? 'Perbarui informasi cabang ini.'
                                : 'Isi informasi cabang baru di bawah ini.'
                        }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-6 px-6">
                    <div class="space-y-2">
                        <Label for="name"> Nama Cabang </Label>
                        <Input
                            :disabled="form.processing"
                            v-model="form.name"
                            id="name"
                            type="text"
                            name="name"
                        />
                        <p v-if="form.errors.name" class="text-xs text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="area_id"> Area </Label>
                        <Select v-model="form.area_id" :disabled="form.processing">
                            <SelectTrigger id="area">
                                <SelectValue placeholder="Pilih Area" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="area in props.areas"
                                    :key="area.id"
                                    :value="area.id"
                                >
                                    {{ area.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.area_id"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.area_id }}
                        </p>
                    </div>
                </form>
                <DialogFooter class="gap-2 px-6 pt-4 pb-6">
                    <DialogClose as-child>
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
                        @click="submit"
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
