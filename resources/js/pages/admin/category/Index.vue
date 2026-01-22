<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Category, Color } from '@/types';

import { ref, computed } from 'vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    CardFooter,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select';
import { Trash2, ChevronDown } from 'lucide-vue-next';
import admin from '@/routes/admin';
import DialogDeleteCategory from '@/components/DialogDeleteCategory.vue';

interface SubCategory {
    id?: number;
    name: string;
    type: string;
    order: number;
    color_id?: number | null;
    color?: Color | null;
    isNew?: boolean;
}

interface CategoriesData {
    [key: string]: Category[];
}

const props = defineProps<{
    categories: CategoriesData;
    colors: Color[];
}>();

// Convert categories to internal state with order
const categoryState = ref<{ [key: string]: SubCategory[] }>({});

// Initialize category state
Object.keys(props.categories).forEach((categoryType) => {
    categoryState.value[categoryType] = props.categories[categoryType].map(
        (cat, index) => ({
            id: cat.id,
            name: cat.name,
            type: cat.type,
            order: index + 1,
            color_id: cat.color_id,
            color: cat.color,
            isNew: false,
        }),
    );
});

// Track which categories are in "adding" mode
const addingCategory = ref<{ [key: string]: boolean }>({});
const newCategoryName = ref<{ [key: string]: string }>({});
const processing = ref<{ [key: string]: boolean }>({});
const openColorDropdown = ref<string | null>(null);

// Get sorted categories for each type
const getSortedCategories = (type: string) => {
    return computed(() => {
        if (!categoryState.value[type]) return [];
        return [...categoryState.value[type]].sort((a, b) => a.order - b.order);
    });
};

// Get max order for category type
const getMaxOrder = (type: string) => {
    return computed(() => categoryState.value[type]?.length || 0);
};

// Format category type to display name
const formatCategoryName = (type: string) => {
    return type
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Get color by id
const getColorById = (colorId?: number | null) => {
    if (!colorId) return null;
    return props.colors.find((c) => c.id === colorId);
};

// Toggle color dropdown
const toggleColorDropdown = (key: string) => {
    if (openColorDropdown.value === key) {
        openColorDropdown.value = null;
    } else {
        openColorDropdown.value = key;
    }
};

// Select color
const selectColor = (type: string, index: number, colorId: number) => {
    const categories = categoryState.value[type];
    if (categories && categories[index]) {
        categories[index].color_id = colorId;
        categories[index].color = getColorById(colorId);
    }
    openColorDropdown.value = null;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    openColorDropdown.value = null;
};

// Start adding new category
const startAdding = (type: string) => {
    addingCategory.value[type] = true;
    newCategoryName.value[type] = '';
};

// Cancel adding
const cancelAdding = (type: string) => {
    addingCategory.value[type] = false;
    newCategoryName.value[type] = '';
};

// Add new sub-category
const addSubCategory = (type: string) => {
    if (!newCategoryName.value[type]?.trim()) return;

    if (!categoryState.value[type]) {
        categoryState.value[type] = [];
    }

    categoryState.value[type].push({
        name: newCategoryName.value[type].trim(),
        type: type,
        order: categoryState.value[type].length + 1,
        color_id: null,
        color: null,
        isNew: true,
    });

    newCategoryName.value[type] = '';
    addingCategory.value[type] = false;
};

// Update order
const updateOrder = (type: string, index: number, newOrder: number) => {
    const categories = categoryState.value[type];
    if (!categories) return;

    const item = categories[index];
    const oldOrder = item.order;

    if (oldOrder === newOrder) return;

    // Update orders
    categories.forEach((cat) => {
        if (cat === item) {
            cat.order = newOrder;
        } else if (oldOrder < newOrder) {
            // Moving down
            if (cat.order > oldOrder && cat.order <= newOrder) {
                cat.order--;
            }
        } else {
            // Moving up
            if (cat.order >= newOrder && cat.order < oldOrder) {
                cat.order++;
            }
        }
    });
};

const deleteDialog = ref({
    open: false,
    type: '',
    index: -1,
});

const deleteCategory = (type: string, index: number) => {
    deleteDialog.value = {
        open: true,
        type,
        index,
    };
};

// Delete category
const confirmDelete = () => {
    const { type, index } = deleteDialog.value;
    const categories = categoryState.value[type];
    const item = categories[index];

    if (item.isNew) {
        // Just remove from array if it's new
        categories.splice(index, 1);
        // Reorder
        categories.forEach((cat, idx) => {
            cat.order = idx + 1;
        });
        deleteDialog.value.open = false;
    } else {
        // Send delete request to backend
        processing.value[type] = true;
        router.delete(admin.categories.destroy(item.id!).url, {
            preserveScroll: true,
            onSuccess: () => {
                categories.splice(index, 1);
                // Reorder
                categories.forEach((cat, idx) => {
                    cat.order = idx + 1;
                });
                deleteDialog.value.open = false;
            },
            onFinish: () => {
                processing.value[type] = false;
            },
            onError: () => {
                processing.value[type] = false;
            },
        });
    }
};

const cancelDelete = () => {
    deleteDialog.value.open = false;
};

// Save changes for a category type
const saveChanges = (type: string) => {
    const categories = categoryState.value[type];
    if (!categories) return;

    processing.value[type] = true;

    const data = {
        categories: categories.map((cat) => ({
            id: cat.id,
            name: cat.name,
            type: cat.type,
            order: cat.order,
            color_id: cat.color_id,
            isNew: cat.isNew,
        })),
    };

    router.post(admin.categories.bulkUpdate().url, data, {
        preserveScroll: true,
        onSuccess: () => {
            // Mark all as not new anymore
            categories.forEach((cat) => {
                cat.isNew = false;
            });
        },
        onFinish: () => {
            processing.value[type] = false;
        },
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: admin.categories.index().url,
    },
];
</script>

<template>
    <Head title="Category Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
            @click="closeDropdown"
        >
            <div
                class="grid grid-cols-1 gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Dynamic Cards for Each Category Type -->
                <Card v-for="(items, type) in categoryState" :key="type">
                    <CardHeader>
                        <CardTitle>{{
                            formatCategoryName(String(type))
                        }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-3">
                            <li
                                v-for="(
                                    subCategory, index
                                ) in getSortedCategories(String(type)).value"
                                :key="subCategory.id || `new-${index}`"
                                class="flex items-center gap-2"
                            >
                                <Input
                                    v-model="subCategory.name"
                                    placeholder="Sub-category name"
                                    class="flex-1"
                                />

                                <!-- Color Selector -->
                                <div class="relative">
                                    <button
                                        @click.stop="
                                            toggleColorDropdown(
                                                `${type}-${index}`,
                                            )
                                        "
                                        class="flex h-10 w-10 items-center justify-center rounded-md border border-input bg-background transition-colors hover:bg-accent"
                                        :class="
                                            subCategory.color
                                                ? subCategory.color.class
                                                : 'bg-gray-100'
                                        "
                                        type="button"
                                    >
                                        <ChevronDown
                                            class="h-4 w-4"
                                            :class="
                                                !subCategory.color
                                                    ? 'text-gray-500'
                                                    : ''
                                            "
                                        />
                                    </button>

                                    <!-- Color Dropdown -->
                                    <div
                                        v-if="
                                            openColorDropdown ===
                                            `${type}-${index}`
                                        "
                                        class="absolute right-0 z-50 mt-2 w-48 rounded-md border border-gray-200 bg-white shadow-lg"
                                        @click.stop
                                    >
                                        <div class="p-2">
                                            <div
                                                class="mb-2 px-2 text-xs font-medium text-gray-500"
                                            >
                                                Select Color
                                            </div>
                                            <div
                                                class="max-h-60 overflow-y-auto"
                                            >
                                                <button
                                                    v-for="color in colors"
                                                    :key="color.id"
                                                    @click="
                                                        selectColor(
                                                            String(type),
                                                            index,
                                                            color.id,
                                                        )
                                                    "
                                                    class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors hover:bg-gray-100"
                                                    :class="
                                                        subCategory.color_id ===
                                                        color.id
                                                            ? 'bg-gray-50'
                                                            : ''
                                                    "
                                                >
                                                    <div
                                                        class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded"
                                                        :class="color.class"
                                                    >
                                                        <div
                                                            v-if="
                                                                subCategory.color_id ===
                                                                color.id
                                                            "
                                                            class="h-2 w-2 rounded-full bg-current"
                                                        ></div>
                                                    </div>
                                                    <span
                                                        class="text-gray-700 capitalize"
                                                        >{{ color.name }}</span
                                                    >
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <Select
                                    :model-value="subCategory.order"
                                    @update:model-value="
                                        (val) =>
                                            updateOrder(
                                                String(type),
                                                index,
                                                Number(val),
                                            )
                                    "
                                >
                                    <SelectTrigger class="w-16">
                                        <SelectValue
                                            :placeholder="
                                                String(subCategory.order)
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="n in getMaxOrder(
                                                String(type),
                                            ).value"
                                            :key="n"
                                            :value="String(n)"
                                        >
                                            {{ n }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="deleteCategory(String(type), index)"
                                    :disabled="processing[type]"
                                >
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </li>
                        </ul>
                        <div
                            v-if="addingCategory[type]"
                            class="mt-4 flex gap-2"
                        >
                            <Input
                                v-model="newCategoryName[type]"
                                placeholder="Enter new sub-category"
                                @keyup.enter="addSubCategory(String(type))"
                                class="flex-1"
                            />
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="cancelAdding(String(type))"
                            >
                                Cancel
                            </Button>
                        </div>
                    </CardContent>
                    <CardFooter class="flex flex-col gap-2">
                        <Button
                            v-if="!addingCategory[type]"
                            @click="startAdding(String(type))"
                            variant="outline"
                            class="w-full"
                            :disabled="processing[type]"
                        >
                            Add Category
                        </Button>
                        <Button
                            v-if="addingCategory[type]"
                            @click="addSubCategory(String(type))"
                            variant="outline"
                            class="w-full"
                        >
                            Save New
                        </Button>
                        <Button
                            @click="saveChanges(String(type))"
                            class="w-full"
                            :disabled="processing[type]"
                        >
                            {{
                                processing[type] ? 'Saving...' : 'Save Changes'
                            }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
        <DialogDeleteCategory
            v-model:open="deleteDialog.open"
            title="Delete Category"
            description="Are you sure you want to delete this category? This action cannot be undone."
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </AppLayout>
</template>

<style scoped></style>
