<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import RlqhLayout from '@/layouts/RlqhLayout.vue';
import { Article, BreadcrumbItem } from '@/types';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { computed, ref } from 'vue';
import ArticleController from '@/actions/App/Http/Controllers/ArticleController';
import rlqh from '@/routes/rlqh';

const props = defineProps<{
    article: Article | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: rlqh.news.authorIndex().url,
    },
    {
        title: 'Buat',
        href: '#',
    },
];

const isEditing = computed(() => !!props.article);
const imagePreview = ref<string | null>(null);
const dragOver = ref(false);
const imageError = ref<string | null>(null);
const imageRemoved = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const MAX_IMAGE_SIZE = 1 * 1024 * 1024;

function validateAndSetImage(file: File) {
    imageError.value = null;

    if (!file) return;

    const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!allowed.includes(file.type)) {
        imageError.value =
            'Format tidak didukung. Gunakan JPG, JPEG, PNG, atau WebP.';
        return;
    }

    if (file.size > MAX_IMAGE_SIZE) {
        imageError.value = `Ukuran gambar (${(file.size / 1024 / 1024).toFixed(2)} MB) melebihi batas 1 MB.`;
        return;
    }

    form.image = file;
    imageRemoved.value = false;
    form.image_removed = false;

    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string | null;
    };
    reader.readAsDataURL(file);
}

function handleFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) validateAndSetImage(file);
}

function handleDrop(e: DragEvent) {
    dragOver.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) validateAndSetImage(file);
}

function openFileDialog() {
    fileInput.value?.click();
}

const form = useForm({
    title: props.article?.title ?? '',
    image: null as File | null,
    image_removed: false,
    content: props.article?.content ?? '',
    status: props.article?.status ?? 'draft',
});

function removeImage() {
    imagePreview.value = null;
    imageRemoved.value = true;
    form.image = null;
    form.image_removed = true;
    if (fileInput.value) fileInput.value.value = '';
}

const submit = (publish: boolean) => {
    imageError.value = null;
    form.clearErrors();

    if (!form.title.trim()) {
        form.setError('title', 'Judul wajib diisi.');
        return;
    }

    if (!form.content.trim()) {
        form.setError('content', 'Konten wajib diisi.');
        return;
    }

    if (imageError.value) return;

    form.status = publish ? 'published' : 'draft';

    const options = {
        forceFormData: true,
        preserveScroll: true,
    };

    if (isEditing.value) {
        const route = ArticleController.update.form(props.article!.id);
        form.submit(route.method, route.action, options);
    } else {
        const route = ArticleController.store.form();
        form.submit(route.method, route.action, options);
    }
};
</script>

<template>
    <Head title="News" />

    <RlqhLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <h1 class="text-2xl font-bold">
                {{ isEditing ? 'Edit Berita' : 'Buat Berita Baru' }}
            </h1>

            <div class="mb-2 flex flex-col gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Judul</label>
                    <Input
                        v-model="form.title"
                        type="text"
                        placeholder="Masukkan judul berita"
                    />
                    <p
                        v-if="form.errors.title"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.title }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label>Gambar</Label>
                    <div v-if="imagePreview || (isEditing && article?.image && !imageRemoved)" class="group relative mb-2">
                        <img
                            :src="imagePreview || `/storage/${article?.image}`"
                            alt="Preview"
                            class="h-auto max-h-80 w-full rounded-xl border border-slate-100 object-contain"
                        />
                        <button
                            type="button"
                            @click="removeImage"
                            class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white/90 text-slate-500 opacity-0 shadow-sm backdrop-blur-sm transition-all duration-150 group-hover:opacity-100 hover:border-rose-200 hover:text-rose-500"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <div
                        v-else
                        class="flex h-28 flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 text-sm text-slate-500"
                        :class="{
                            'border-blue-400 bg-blue-50': dragOver,
                        }"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            class="hidden"
                            accept="image/jpeg,image/png,image/webp,image/jpg"
                            @change="handleFileChange"
                        />
                        <span>Tarik & lepas gambar di sini</span>
                        <Button
                            type="button"
                            variant="outline"
                            @click="openFileDialog"
                        >
                            Pilih Gambar
                        </Button>
                    </div>

                    <p v-if="imageError" class="mt-1 text-sm text-red-500">
                        {{ imageError }}
                    </p>
                </div>
            </div>

            <div class="mb-2">
                <label class="mb-1 block text-sm font-medium">Konten</label>
                <TiptapEditor v-model="form.content" />
                <p
                    v-if="form.errors.content"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.content }}
                </p>
            </div>

            <div class="flex justify-end space-x-2">
                <Button
                    class="cursor-pointer"
                    type="submit"
                    :disabled="form.processing"
                    variant="outline"
                    @click="submit(false)"
                >
                    Simpan Sebagai Draft
                </Button>
                <Button
                    class="cursor-pointer"
                    type="submit"
                    :disabled="form.processing"
                    @click="submit(true)"
                >
                    Publikasikan
                </Button>
            </div>
        </div>
    </RlqhLayout>
</template>

<style scoped></style>
