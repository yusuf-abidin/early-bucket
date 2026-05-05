<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Branch, Category, StcTlContact } from '@/types';
import { computed, ref, watch } from 'vue';
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
import {
    Popover,
    PopoverAnchor,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    ListboxContent,
    ListboxFilter,
    ListboxItem,
    ListboxItemIndicator,
    ListboxRoot,
    useFilter,
} from 'reka-ui';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';
import { ChevronDown, CheckIcon } from 'lucide-vue-next';

const props = defineProps<{
    categories: Category[];
}>();

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
    categories: [] as number[],
    phone: '',
    role: '',
});

const closeModal = () => {
    categoriesRef.value = [];
    editStcTlContact.value = null;
    formStcTlIsOpen.value = false;
    form.reset();
    form.clearErrors();
};

const categoriesRef = ref<number[]>([]);
const searchTerm = ref('');
const popoverOpen = ref(false);

const { contains } = useFilter({ sensitivity: 'base' });

const selectedCategories = computed(() =>
    categoriesRef.value
        .map((id) => props.categories.find((category) => category.id === id))
        .filter((category): category is Category => Boolean(category)),
);

const deleteCategory = (category: Category) => {
    categoriesRef.value = categoriesRef.value.filter(
        (id: number) => id !== category.id,
    );
};

const filteredCategories = computed(() =>
    searchTerm.value === ''
        ? props.categories
        : props.categories.filter((category) =>
              contains(category.name, searchTerm.value),
          ),
);

watch(categoriesRef, (newCategoriesRef) => {
    form.categories = newCategoriesRef;
});

watch(searchTerm, (f) => {
    if (f) {
        popoverOpen.value = true;
    }
});

const deleteContact = () => {
    if (!editStcTlContact.value?.contact) return;
    router.delete(stcTlContact.destroy(editStcTlContact.value.contact.id).url, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

watch(
    () => editStcTlContact.value,
    (data) => {
        if (!data) return;

        form.name = data.contact?.name ?? '';
        form.phone = data.contact?.phone ?? '';
        categoriesRef.value =
        data.contact?.categories.map((c: Category) => c.id) ?? [];
        form.categories = categoriesRef.value;
        form.branch_id = data.branch.id;
        form.role = data.role;

    },
    {
        immediate: true,
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
                        <template v-else> Tambah Kontak </template>
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
                        <Label for="bucket">Bucket</Label>
                        <Popover v-model:open="popoverOpen">
                            <ListboxRoot
                                v-model="categoriesRef"
                                highlight-on-hover
                                multiple
                            >
                                <PopoverAnchor>
                                    <TagsInput
                                        :disabled="form.processing"
                                        :model-value="
                                            selectedCategories.map(
                                                (c) => c.name,
                                            )
                                        "
                                        class="flex-wrap w-fit"
                                    >
                                        <TagsInputItem
                                            v-for="category in selectedCategories"
                                            :key="category.name"
                                            :value="category.name"
                                            class="w-auto shrink-0"
                                        >
                                            <TagsInputItemText />
                                            <TagsInputItemDelete
                                                @click="
                                                    deleteCategory(category)
                                                "
                                            />
                                        </TagsInputItem>

                                        <ListboxFilter
                                            v-model="searchTerm"
                                            as-child
                                        >
                                            <TagsInputInput
                                                :disabled="form.processing"
                                                placeholder="Pilih Bucket"
                                                @keydown.enter.prevent
                                                @keydown.down="
                                                    popoverOpen = true
                                                "
                                            />
                                        </ListboxFilter>

                                        <PopoverTrigger as-child>
                                            <Button
                                                type="button"
                                                size="icon-sm"
                                                variant="ghost"
                                                class="order-last ml-auto self-start"
                                            >
                                                <ChevronDown class="size-3.5" />
                                            </Button>
                                        </PopoverTrigger>
                                    </TagsInput>
                                </PopoverAnchor>

                                <PopoverContent
                                    class="p-1"
                                    align="start"
                                    @open-auto-focus.prevent
                                >
                                    <ListboxContent
                                        class="max-h-[300px] scroll-py-1 overflow-x-hidden overflow-y-auto empty:p-1 empty:after:block empty:after:content-['No_options']"
                                        tabindex="0"
                                    >
                                        <ListboxItem
                                            v-for="item in filteredCategories"
                                            :key="item.id"
                                            class="relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&_svg:not([class*='text-'])]:text-muted-foreground"
                                            :value="item.id"
                                            @select="
                                                () => {
                                                    searchTerm = '';
                                                }
                                            "
                                        >
                                            <span>{{ item.name }}</span>
                                            <ListboxItemIndicator
                                                class="ml-auto inline-flex items-center justify-center"
                                            >
                                                <CheckIcon />
                                            </ListboxItemIndicator>
                                        </ListboxItem>
                                    </ListboxContent>
                                </PopoverContent>
                            </ListboxRoot>
                        </Popover>
                        <p
                            v-if="form.errors.categories"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.categories }}
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
