<script setup lang="ts">
import { Category, Memo, User } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { CalendarDate, DateValue, parseDate } from '@internationalized/date';
import {
    ListboxContent,
    ListboxFilter,
    ListboxItem,
    ListboxItemIndicator,
    ListboxRoot,
    useFilter,
} from 'reka-ui';
import { Label } from '@/components/ui/label';
import MemoController from '@/actions/App/Http/Controllers/MemoController';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    DialogHeader,
} from '@/components/ui/dialog';
import {
    Popover,
    PopoverAnchor,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import {
    CalendarIcon,
    CheckIcon,
    ChevronDown,
    Copy,
    ExternalLink,
} from 'lucide-vue-next';
import { Calendar } from '@/components/ui/calendar';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';
import { Input } from '@/components/ui/input';
import { toast } from 'vue-sonner';
import { ScrollArea } from '@/components/ui/scroll-area';

const isOpen = defineModel<boolean>('isOpen', { default: false });
const selectedMemo = defineModel<Memo | null>('selectedMemo', {
    default: null,
});
const props = defineProps<{
    usersData: { id: number; name: string }[];
    categories: Category[];
}>();

const form = useForm({
    received_at: '',
    origin: '',
    reference_number: '',
    users: [] as number[],
    due_date: '',
    subject: '',
    document_link: '',
    category_id: '',
    follow_up_note: '',
});

const selectedReceivedAtDate = ref<DateValue | undefined>(undefined);
const selectedDeadlineDate = ref<DateValue | undefined>(undefined);

const deletePIC = (user: User) => {
    usersRef.value = usersRef.value.filter((id: number) => id !== user.id);
};

const usersRef = ref<number[]>([]);
const searchTerm = ref('');
const popoverOpen = ref(false);

const selectedUsers = computed(() =>
    usersRef.value
        .map((id) => props.usersData.find((user) => user.id === id))
        .filter((user): user is User => Boolean(user)),
);

const { contains } = useFilter({ sensitivity: 'base' });
const filteredUsers = computed(() =>
    searchTerm.value === ''
        ? props.usersData
        : props.usersData.filter((option) =>
              contains(option.name, searchTerm.value),
          ),
);

watch(usersRef, (newUsersRef) => {
    form.users = newUsersRef;
});

watch(searchTerm, (f) => {
    if (f) {
        popoverOpen.value = true;
    }
});

watch(selectedDeadlineDate, (newDate) => {
    if (newDate) {
        form.due_date = `${newDate.year}-${String(newDate.month).padStart(2, '0')}-${String(newDate.day).padStart(2, '0')}`;
    } else {
        form.due_date = '';
    }
});

watch(selectedReceivedAtDate, (newDate) => {
    if (newDate) {
        // Convert DateValue ke format YYYY-MM-DD untuk backend
        form.received_at = `${newDate.year}-${String(newDate.month).padStart(2, '0')}-${String(newDate.day).padStart(2, '0')}`;
    } else {
        form.received_at = '';
    }
});

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const closeModal = () => {
    form.reset();
    usersRef.value = [];
    selectedDeadlineDate.value = undefined;
    selectedReceivedAtDate.value = undefined;
    isOpen.value = false;
    selectedMemo.value = null;
    form.clearErrors();
};

const submit = () => {
    const originalReceivedAtDate = form.received_at;
    const originalDueDate = form.due_date;

    if (form.received_at) {
        form.received_at = `${form.received_at} 23:59:59`;
    }
    if (form.due_date) {
        form.due_date = `${form.due_date} 23:59:59`;
    }

    const options = {
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            form.received_at = originalReceivedAtDate;
            form.due_date = originalDueDate;
        },
    };

    if (!selectedMemo.value) {
        const route = MemoController.store.form();
        form.submit(route.method, route.action, options);
    } else {
        const route = MemoController.update.form(selectedMemo.value.id);
        form.submit(route.method, route.action, options);
    }
};

watch(
    () => selectedMemo.value,
    (newMemo) => {
        if (newMemo) {
            const normalizedMemo = {
                received_at: newMemo.received_at ?? '',
                category_id: newMemo.category_id
                    ? String(newMemo.category_id)
                    : '',
                users: newMemo.users?.map((u: User) => u.id) ?? [],
                origin: newMemo.origin ?? '',
                document_link: newMemo.document_link ?? '',
                due_date: newMemo.due_date ?? '',
                reference_number: newMemo.reference_number ?? '',
                subject: newMemo.subject ?? '',
                follow_up_note: newMemo.follow_up_note ?? '',
            };

            usersRef.value = normalizedMemo.users;

            if (normalizedMemo.received_at) {
                try {
                    selectedReceivedAtDate.value = parseDate(
                        normalizedMemo.received_at,
                    );
                } catch (e) {
                    console.log('Error parsing date: ', e);
                    selectedReceivedAtDate.value = undefined;
                }
            } else {
                selectedReceivedAtDate.value = undefined;
            }

            if (normalizedMemo.due_date) {
                try {
                    selectedDeadlineDate.value = parseDate(
                        normalizedMemo.due_date,
                    );
                } catch (e) {
                    console.log('Error parsing date: ', e);
                    selectedDeadlineDate.value = undefined;
                }
            }

            form.defaults(normalizedMemo);
            form.reset();
        } else {
            form.defaults({
                received_at: '',
                category_id: '',
                document_link: '',
                users: [],
                origin: '',
                due_date: '',
                reference_number: '',
                subject: '',
                follow_up_note: '',
            });
            form.reset();
            usersRef.value = [];
            selectedReceivedAtDate.value = undefined;
            selectedDeadlineDate.value = undefined;
        }
    },
);

const copyToClipboard = (text: string) => {
    if (!text) return;
    navigator.clipboard.writeText(text);
    toast.success('Link berhasil disalin!', {
        position: 'top-right',
    });
};

const openLink = (url: string) => {
    if (!url) return;
    // Pastikan ada protokol
    const finalUrl = url.startsWith('http') ? url : 'https://' + url;
    window.open(finalUrl, '_blank', 'noopener,noreferrer');
};
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-scroll p-0 sm:max-w-2xl lg:max-w-4xl xl:max-w-5xl"
        >

            <ScrollArea>
            <DialogHeader class="px-6 pt-6">
                <DialogTitle class="text-2xl font-semibold">
                    {{ selectedMemo !== null ? 'Edit Memo' : 'Buat Memo Baru' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        selectedMemo !== null
                            ? 'Perbarui detail memo yang sudah ada'
                            : 'Lengkapi formulir untuk membuat memo baru'
                    }}
                </DialogDescription>
            </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4 px-6 pb-6">
                    <!-- Grid 2 kolom untuk desktop -->
                    <div class="grid gap-6 md:grid-cols-2 items-start">
                        <!-- TANGGAL MASUK -->
                        <div class="space-y-2">
                            <Label
                                for="received_at"
                                class="text-sm font-medium"
                            >
                                Tanggal Masuk
                                <span class="text-destructive">*</span>
                            </Label>
                            <Popover v-slot="{ close }">
                                <PopoverTrigger as-child>
                                    <Button
                                        id="received_at"
                                        variant="outline"
                                        :class="[
                                            'w-full justify-start text-left font-normal',
                                            !selectedReceivedAtDate &&
                                                'text-muted-foreground',
                                        ]"
                                        :disabled="form.processing"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        <span v-if="selectedReceivedAtDate">
                                            {{ formatDate(form.received_at) }}
                                        </span>
                                        <span v-else>Pilih Tanggal</span>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent
                                    class="w-auto p-0"
                                    align="start"
                                >
                                    <Calendar
                                        v-model="selectedReceivedAtDate"
                                        layout="month-and-year"
                                        initial-focus
                                        :min-value="
                                            new CalendarDate(2025, 1, 1)
                                        "
                                        @update:model-value="(value) => {
                                            if (value) selectedReceivedAtDate = value;
                                            close();
                                        }"
                                    />
                                </PopoverContent>
                            </Popover>
                            <p
                                v-if="form.errors.received_at"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.received_at }}
                            </p>
                        </div>

                        <!-- DEADLINE -->
                        <div class="space-y-2">
                            <Label for="due_date" class="text-sm font-medium">
                                Deadline
                            </Label>
                            <Popover v-slot="{ close }">
                                <PopoverTrigger as-child>
                                    <Button
                                        id="due_date"
                                        variant="outline"
                                        :class="[
                                            'w-full justify-start text-left font-normal',
                                            !selectedDeadlineDate &&
                                                'text-muted-foreground',
                                        ]"
                                        :disabled="form.processing"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        <span v-if="selectedDeadlineDate">
                                            {{ formatDate(form.due_date) }}
                                        </span>
                                        <span v-else>Pilih Tanggal</span>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent
                                    class="w-auto p-0"
                                    align="start"
                                >
                                    <Calendar
                                        v-model="selectedDeadlineDate"
                                        layout="month-and-year"
                                        initial-focus
                                        :min-value="
                                            new CalendarDate(2025, 1, 1)
                                        "
                                        @update:model-value="(value) => {
                                            if (value) selectedDeadlineDate = value;
                                            close();
                                        }"
                                    />
                                </PopoverContent>
                            </Popover>
                            <p
                                v-if="form.errors.due_date"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.due_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Asal (full width) -->
                    <div class="space-y-2">
                        <Label for="origin" class="text-sm font-medium">
                            Asal
                        </Label>
                        <Textarea
                            :disabled="form.processing"
                            id="origin"
                            name="origin"
                            v-model="form.origin"
                            placeholder="Masukkan asal surat"
                            rows="3"
                            class="resize-none"
                        />
                        <p
                            v-if="form.errors.origin"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.origin }}
                        </p>
                    </div>

                    <!-- Nomor (full width) -->
                    <div class="space-y-2">
                        <Label
                            for="reference_number"
                            class="text-sm font-medium"
                        >
                            Nomor Surat
                        </Label>
                        <Textarea
                            :disabled="form.processing"
                            id="reference_number"
                            name="reference_number"
                            v-model="form.reference_number"
                            placeholder="Masukkan nomor surat"
                            rows="3"
                            class="resize-none"
                        />
                        <p
                            v-if="form.errors.reference_number"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.reference_number }}
                        </p>
                    </div>

                    <!-- Perihal (full width) -->
                    <div class="space-y-2">
                        <Label for="subject" class="text-sm font-medium">
                            Perihal
                        </Label>
                        <Textarea
                            :disabled="form.processing"
                            id="subject"
                            name="subject"
                            v-model="form.subject"
                            placeholder="Masukkan perihal surat"
                            rows="4"
                            class="resize-none"
                        />
                        <p
                            v-if="form.errors.subject"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.subject }}
                        </p>
                    </div>

                    <!-- PIC & Kategori dalam 2 kolom -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- PIC -->
                        <div class="space-y-2">
                            <Label for="users" class="text-sm font-medium">
                                PIC (Penanggung Jawab)
                            </Label>
                            <Popover v-model:open="popoverOpen">
                                <ListboxRoot
                                    v-model="usersRef"
                                    highlight-on-hover
                                    multiple
                                >
                                    <PopoverAnchor
                                        class="inline-flex w-[300px]"
                                    >
                                        <TagsInput
                                            :disabled="form.processing"
                                            :model-value="
                                                selectedUsers.map((u) => u.name)
                                            "
                                            class="w-full"
                                        >
                                            <TagsInputItem
                                                v-for="user in selectedUsers"
                                                :key="user.name"
                                                :value="user.name"
                                            >
                                                <TagsInputItemText />
                                                <TagsInputItemDelete
                                                    @click="deletePIC(user)"
                                                />
                                            </TagsInputItem>

                                            <ListboxFilter
                                                v-model="searchTerm"
                                                as-child
                                            >
                                                <TagsInputInput
                                                    :disabled="form.processing"
                                                    placeholder="Pilih PIC"
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
                                                    <ChevronDown
                                                        class="size-3.5"
                                                    />
                                                </Button>
                                            </PopoverTrigger>
                                        </TagsInput>
                                    </PopoverAnchor>

                                    <PopoverContent
                                        class="p-1"
                                        @open-auto-focus.prevent
                                    >
                                        <ListboxContent
                                            class="max-h-[300px] scroll-py-1 overflow-x-hidden overflow-y-auto empty:p-1 empty:after:block empty:after:content-['Tidak_ada_opsi']"
                                            tabindex="0"
                                        >
                                            <ListboxItem
                                                v-for="item in filteredUsers"
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
                                v-if="form.errors.users"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.users }}
                            </p>
                        </div>

                        <!-- Kategori -->
                        <div class="space-y-2">
                            <Label
                                for="category_id"
                                class="text-sm font-medium"
                                :class="{
                                    'text-destructive': form.errors.category_id,
                                }"
                            >
                                Sifat
                                <span class="text-destructive">*</span>
                            </Label>
                            <Select
                                v-model="form.category_id"
                                :disabled="form.processing"
                            >
                                <SelectTrigger id="category">
                                    <SelectValue placeholder="Pilih kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="category in props.categories"
                                        :key="category.id"
                                        :value="String(category.id)"
                                    >
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.category_id"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.category_id }}
                            </p>
                        </div>
                    </div>

                    <!-- LINK (full width) -->
                    <div class="space-y-2">
                        <Label for="document_link" class="text-sm font-medium">
                            Link Dokumen
                        </Label>

                        <div class="flex gap-2">
                            <Input
                                :disabled="form.processing"
                                id="document_link"
                                name="document_link"
                                v-model="form.document_link"
                                type="url"
                                placeholder="https://drive.google.com/..."
                                class="flex-1"
                            />

                            <Button
                                class="shrink-0"
                                type="button"
                                variant="outline"
                                size="icon"
                                :disabled="
                                    !form.document_link || form.processing
                                "
                                @click="copyToClipboard(form.document_link)"
                                title="Salin link"
                            >
                                <Copy class="h-4 w-4" />
                            </Button>

                            <Button
                                class="shrink-0"
                                type="button"
                                variant="secondary"
                                size="icon"
                                :disabled="
                                    !form.document_link || form.processing
                                "
                                @click="openLink(form.document_link)"
                                title="Buka link"
                            >
                                <ExternalLink class="h-4 w-4" />
                            </Button>
                        </div>

                        <p
                            v-if="form.errors.document_link"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.document_link }}
                        </p>
                    </div>

                    <!-- Tindak Lanjut (full width) -->
                    <div class="space-y-2">
                        <Label for="follow_up_note" class="text-sm font-medium">
                            Tindak Lanjut
                        </Label>
                        <Textarea
                            :disabled="form.processing"
                            id="follow_up_note"
                            name="follow_up_note"
                            v-model="form.follow_up_note"
                            placeholder="Masukkan catatan tindak lanjut"
                            rows="4"
                            class="resize-none"
                        />
                        <p
                            v-if="form.errors.follow_up_note"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.follow_up_note }}
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
                    type="submit"
                    @click="submit"
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
