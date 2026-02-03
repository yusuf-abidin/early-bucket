<script setup lang="ts">
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverAnchor,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from '@/components/ui/tags-input';
import { Textarea } from '@/components/ui/textarea';
import { Category, Task, User } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { CheckIcon, ChevronDown, CalendarIcon } from 'lucide-vue-next';
import {
    ListboxContent,
    ListboxFilter,
    ListboxItem,
    ListboxItemIndicator,
    ListboxRoot,
    useFilter,
} from 'reka-ui';
import { computed, ref, watch } from 'vue';
import {
    parseDate,
    type DateValue,
    CalendarDate,
} from '@internationalized/date';
import { Calendar } from '@/components/ui/calendar';
import DebtorSavingsController from '@/actions/App/Http/Controllers/DebtorSavingsController';

const isOpen = defineModel<boolean>('formIsOpen', { default: false });
const taskData = defineModel<Task | null>('selectedData', { default: null });

const props = withDefaults(
    defineProps<{
        usersData: User[];
        categories: Category[];
        mode?: 'pending_matter' | 'debtor_savings';
    }>(),
    {
        mode: 'pending_matter',
    },
);

const form = useForm({
    task_description: '',
    category_id: '',
    users: [] as number[],
    due_date: '',
    notes: '',
});

// State untuk calendar
const selectedDate = ref<DateValue | undefined>(undefined);

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

// Watch untuk sync calendar dengan form
watch(selectedDate, (newDate) => {
    if (newDate) {
        // Convert DateValue ke format YYYY-MM-DD untuk backend
        form.due_date = `${newDate.year}-${String(newDate.month).padStart(2, '0')}-${String(newDate.day).padStart(2, '0')}`;
    } else {
        form.due_date = '';
    }
});

// Helper function untuk format tanggal tampilan
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
    selectedDate.value = undefined;
    isOpen.value = false;
    taskData.value = null;
    form.clearErrors();
};

const submit = () => {
    // 1. Simpan nilai asli (YYYY-MM-DD) agar bisa dikembalikan jika error
    const originalDate = form.due_date;

    // 2. Jika ada tanggal, tambahkan waktu akhir hari (23:59:59)
    if (form.due_date) {
        // Format: 2026-01-20 23:59:59
        // Laravel akan otomatis mengenali format ini sebagai valid datetime
        form.due_date = `${form.due_date} 23:59:59`;
    }

    const options = {
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            // 3. Jika error (misal: validasi gagal), kembalikan ke format semula
            // supaya komponen kalender tidak error saat render ulang
            form.due_date = originalDate;
        },
        onFinish: () => {
            // Opsional untuk Update
            if (taskData.value) closeModal();
        },
    };

    if (!taskData.value) {
        let route = null;
        if (props.mode === 'pending_matter') {
            route = TaskController.store.form();
        } else {
            route = DebtorSavingsController.store.form();
        }
        form.submit(route!.method, route!.action, options);
    } else {
        let route = null;
        if (props.mode === 'pending_matter') {
            route = TaskController.update.form(taskData.value.id);
        } else {
            route = DebtorSavingsController.update.form(taskData.value.id);
        }
        form.submit(route.method, route.action, options);
    }
};

watch(
    () => taskData.value,
    (newTask) => {
        if (newTask) {
            const normalizedTask = {
                task_description: newTask.task_description ?? '',
                category_id: newTask.category_id
                    ? String(newTask.category_id)
                    : '',
                users: newTask.users?.map((u: User) => u.id) ?? [],
                due_date: newTask.due_date ?? '',
                notes: newTask.notes ?? '',
            };

            usersRef.value = normalizedTask.users;

            // Set selectedDate dari due_date
            if (normalizedTask.due_date) {
                try {
                    selectedDate.value = parseDate(normalizedTask.due_date);
                } catch (e) {
                    console.error('Error parsing date:', e);
                    selectedDate.value = undefined;
                }
            } else {
                selectedDate.value = undefined;
            }

            form.defaults(normalizedTask);
            form.reset();
        } else {
            form.defaults({
                task_description: '',
                category_id: '',
                users: [],
                due_date: '',
                notes: '',
            });
            form.reset();
            usersRef.value = [];
            selectedDate.value = undefined;
        }
    },
);
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-scroll sm:max-w-2xl lg:max-w-4xl xl:max-w-5xl"
        >
            <form @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>
                        {{ taskData !== null ? 'Ubah Data' : 'Tambah Data' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            taskData !== null
                                ? 'Edit rincian data'
                                : 'Tambah data baru'
                        }}
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4">
                    <!-- Agenda (Full Width) -->
                    <div class="grid gap-3">
                        <Label for="task_description"
                            >Agenda<span class="text-destructive"
                                >*</span
                            ></Label
                        >
                        <Textarea
                            :disabled="form.processing"
                            id="task_description"
                            name="task_description"
                            v-model="form.task_description"
                            class="resize-none"
                        />
                        <p
                            v-if="form.errors.task_description"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.task_description }}
                        </p>
                    </div>

                    <!-- Two Column Layout for PIC, Deadline, and Category -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- PIC -->
                        <div class="grid gap-3">
                            <Label for="users">PIC</Label>
                            <Popover v-model:open="popoverOpen">
                                <ListboxRoot
                                    v-model="usersRef"
                                    highlight-on-hover
                                    multiple
                                >
                                    <PopoverAnchor class="inline-flex w-full">
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
                                            class="max-h-[300px] scroll-py-1 overflow-x-hidden overflow-y-auto empty:p-1 empty:after:block empty:after:content-['No_options']"
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
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.users }}
                            </p>
                        </div>

                        <!-- Deadline -->
                        <div class="grid gap-3">
                            <Label for="due_date"
                                >Deadline
                                <span class="text-destructive">*</span></Label
                            >
                            <Popover v-slot="{ close }">
                                <PopoverTrigger as-child>
                                    <Button
                                        id="due_date"
                                        variant="outline"
                                        :class="[
                                            'w-full justify-start text-left font-normal',
                                            !selectedDate &&
                                                'text-muted-foreground',
                                        ]"
                                        :disabled="form.processing"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        <span v-if="selectedDate">
                                            {{ formatDate(form.due_date) }}
                                        </span>
                                        <span v-else> Pilih tanggal </span>
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent
                                    class="w-auto p-0"
                                    align="start"
                                >
                                    <Calendar
                                        v-model="selectedDate"
                                        layout="month-and-year"
                                        initial-focus
                                        :min-value="
                                            new CalendarDate(2025, 1, 1)
                                        "
                                        @update:model-value="close"
                                    />
                                </PopoverContent>
                            </Popover>
                            <p
                                v-if="form.errors.due_date"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.due_date }}
                            </p>
                        </div>

                        <!-- Kategori (Span 2 columns on larger screens) -->
                        <div class="grid gap-3 sm:col-span-2">
                            <Label
                                for="category_id"
                                :class="{
                                    'text-destructive': form.errors.category_id,
                                }"
                            >
                                Kategori
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
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.category_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes (Full Width) -->
                    <div class="grid gap-3 pb-3">
                        <Label for="notes">Notes</Label>
                        <Textarea
                            :disabled="form.processing"
                            id="notes"
                            name="notes"
                            v-model="form.notes"
                            class="resize-none"
                        />
                        <p
                            v-if="form.errors.notes"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeModal"
                        >
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Processing...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
