<script setup lang="ts">
import { Regional } from '@/types';
import { useForm } from '@inertiajs/vue3';
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

const formRegionalIsOpen = defineModel<boolean>('formRegionalIsOpen', {
    default: false,
});
const selectedRegional = defineModel<Regional | null>('selectedRegional', {
    default: null,
});


const closeModal = () => {
    formRegionalIsOpen.value = false;
    selectedRegional.value = null;
    form.reset();
    form.clearErrors();
};

const form = useForm({
    name: '',
});

const submit = () => {};

watch(
    () => selectedRegional.value,
    (newRegional) => {
        if (newRegional) {
            const data = {
                name: newRegional.name ?? '',
            };
            form.defaults(data);
            form.reset();
        } else {
            form.defaults({
                name: '',
            });
            form.reset();
        }
    },
);
</script>

<template>
    <Dialog v-model:open="formRegionalIsOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea>
                <DialogHeader class="px-6 pt-6">
                    <DialogTitle class="text-2xl font-semibold">
                        {{
                            selectedRegional
                                ? 'Edit Regional'
                                : 'Tambah Regional Baru'
                        }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            selectedRegional
                                ? 'Perbarui informasi regional ini.'
                                : 'Isi informasi regional baru di bawah ini.'
                        }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-6 px-6 pb-6">
                    <div class="space-y-2">
                        <Label for="name">
                            Nama Regional
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
