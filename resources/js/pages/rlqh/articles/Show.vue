<script setup lang="ts">
import RlqhLayout from '@/layouts/RlqhLayout.vue';
import { Article } from '@/types';
import DOMPurify from 'dompurify';
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import rlqh from '@/routes/rlqh';

const props = defineProps<{
    article: Article;
}>();

const user = usePage().props.auth?.user;

const breadcrumbs = computed(() => [
    {
        title: 'News',
        href:
            user && user.role === 'admin'
                ? rlqh.news.authorIndex().url
                : rlqh.news.index().url,
    },
    {
        title: props.article.title,
        href: '#',
    },
]);

const cleanedContent = computed(() =>
    DOMPurify.sanitize(props.article.content),
);

const publishedAtText = computed(() => {
    if (!props.article.published_at) return '-';
    const date = new Date(props.article.published_at);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const dialog = ref<HTMLDialogElement | null>(null);

function openImage() {
    dialog.value?.showModal();
}

function closeImage() {
    dialog.value?.close();
}
</script>

<template>
    <Head :title="article.title" />

    <RlqhLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="mx-auto w-full max-w-4xl rounded-xl border bg-white p-6 shadow"
            >
                <div v-if="article.image" class="mb-4 flex justify-center">
                    <img
                        :src="`/storage/${article.image}`"
                        alt="Gambar Artikel"
                        class="max-h-80 w-auto cursor-zoom-in rounded-lg border object-contain transition-opacity hover:opacity-80"
                        @click="openImage"
                    />
                </div>

                <h1
                    class="mb-2 text-center text-2xl leading-tight font-bold text-slate-800"
                >
                    {{ article.title }}
                </h1>
                <div class="mb-4 text-center text-sm text-slate-500">
                    Dipublikasikan: {{ publishedAtText }}
                </div>
                <div
                    v-html="cleanedContent"
                    class="mx-auto prose max-w-none"
                ></div>
            </div>
        </div>

        <!-- Native dialog lightbox -->
        <dialog
            ref="dialog"
            class="fixed top-1/2 left-1/2 max-h-[90vh] max-w-[90vw] -translate-x-1/2 -translate-y-1/2 animate-in rounded-xl border-0 p-2 shadow-2xl duration-200 outline-none zoom-in-95 fade-in backdrop:bg-black/70"
            @click="closeImage"
        >
            <img
                :src="article.image ? `/storage/${article.image}` : ''"
                alt="Gambar Artikel"
                class="max-h-[85vh] max-w-[85vw] rounded-lg object-contain"
            />
        </dialog>
    </RlqhLayout>
</template>

<style scoped>
dialog::backdrop {
    backdrop-filter: blur(2px);
}
</style>
