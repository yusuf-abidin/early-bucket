<script setup lang="ts">
import { Area, Regional } from '@/types';
import { useForm } from '@inertiajs/vue3';
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
import AreaController from '@/actions/App/Http/Controllers/AreaController';
import { watch } from 'vue';
import { Select, SelectContent, SelectItem, SelectValue, SelectTrigger } from '@/components/ui/select';

const props = defineProps<{
    regionals: Regional[];
}>();
const formAreaIsOpen = defineModel<boolean>('formAreaIsOpen', {
    default: false,
});
const selectedArea = defineModel<Area | null>('selectedArea', {
    default: null,
});

const closeModal = () => {
    formAreaIsOpen.value = false;
    selectedArea.value = null;
    form.reset();
    form.clearErrors();
};

const form = useForm({
    regional_id: '' as string | number,
    name: '',
});

const submit = () => {
    const options = {
        onSuccess: () => {
            closeModal();
        },
        onFinish: () => {
            if (selectedArea.value) closeModal();
        },
    };

    if (!selectedArea.value) {
        const route = AreaController.store.form();
        form.submit(route.method, route.action, options);
    } else {
        const route = AreaController.update.form(selectedArea.value.id);
        form.submit(route.method, route.action, options);
    }
};

watch(
    () => selectedArea.value,
    (newArea) => {
        if (newArea) {
            const data = {
                regional_id: newArea.regional_id ?? '',
                name: newArea.name ?? '',
            };
            form.defaults(data);
            form.reset();
        } else {
            form.defaults({
                name: '',
                regional_id: '',
            });
            form.reset();
        }
    },
);
</script>

<template>
    <Dialog v-model:open="formAreaIsOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea>
                <DialogHeader class="px-6 pt-6">
                    <DialogTitle class="text-2xl font-semibold">
                        {{ selectedArea ? 'Edit Area' : 'Tambah Area Baru' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            selectedArea
                                ? 'Perbarui informasi area ini.'
                                : 'Isi informasi area baru di bawah ini.'
                        }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-6 px-6 pb-6">
                    <div class="space-y-2">
                        <Label for="regional_id">
                            Regional
                            <span class="text-destructive">*</span>
                        </Label>
                        <Select
                            v-model="form.regional_id"
                            :disabled="form.processing"
                        >
                         <SelectTrigger id="regional">
                             <SelectValue placeholder="Pilih Regional" />
                         </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="regional in props.regionals"
                                    :key="regional.id"
                                    :value="regional.id"
                                >
                                    {{ regional.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.regional_id"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.regional_id }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="name">
                            Nama Area
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
