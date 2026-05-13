<script setup lang="ts">
import { Article } from '@/types';
import { Separator } from '@/components/ui/separator';
import DOMPurify from 'dompurify';
import { ref, computed, onMounted, onUpdated } from 'vue';

const props = defineProps<{
    article: Article;
    isAdmin?: boolean;
}>();

const emit = defineEmits(['edit', 'delete', 'open']);

const fallbackImage = '/image-fallback.jpg';
const showMenu = ref(false);
const cardRef = ref<HTMLElement | null>(null);
const isTitleClamped = ref(false);

function onImgError(e: Event) {
    (e.target as HTMLImageElement).src = fallbackImage;
}

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

const statusLabel = computed(() =>
    props.article.status === 'published' ? 'Dipublikasikan' : 'Draft',
);

function checkTitleClamped() {
    if (cardRef.value) {
        const titleEl = cardRef.value.querySelector('.line-clamp-2');
        if (titleEl) {
            isTitleClamped.value = titleEl.scrollHeight > titleEl.clientHeight + 1;
        }
    }
}

function handleCardClick(e: MouseEvent) {
    // Hindari klik pada menu/edit/delete
    const path = e.composedPath();
    if (path.some((el: any) => el?.classList?.contains('card-action-btn'))) return;
    emit('open', props.article);
}

onMounted(checkTitleClamped);
onUpdated(checkTitleClamped);
</script>

<template>
    <div
        ref="cardRef"
        class="relative flex flex-col overflow-hidden rounded-xl border border-border bg-white p-4 shadow-md cursor-pointer"
        @click="handleCardClick"
    >
        <div class="relative">
            <img
                :src="props.article.image ? `/storage/${props.article.image}` : fallbackImage"
                :alt="props.article.title"
                class="h-48 w-full bg-slate-100 object-cover"
                @error="onImgError"
            />
            <div
                v-if="props.isAdmin"
                class="absolute top-2 left-2 z-10 flex items-center gap-2"
            >
                <span
                    class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium"
                    :class="props.article.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'"
                >
                    {{ statusLabel }}
                </span>
            </div>
            <div v-if="props.isAdmin" class="absolute top-2 right-2 z-10">
                <div class="group relative">
                    <button
                        @click.stop="showMenu = !showMenu"
                        class="rounded p-1 hover:bg-slate-100 card-action-btn"
                    >
                        <svg
                            width="20"
                            height="20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <circle cx="5" cy="12" r="1.5" />
                            <circle cx="12" cy="12" r="1.5" />
                            <circle cx="19" cy="12" r="1.5" />
                        </svg>
                    </button>
                    <div
                        v-if="showMenu"
                        class="absolute right-0 z-10 mt-2 w-28 rounded border bg-white shadow"
                    >
                        <button
                            @click.stop="$emit('edit', props.article)"
                            class="block w-full cursor-pointer px-4 py-2 text-left hover:bg-slate-100 card-action-btn"
                        >
                            Edit
                        </button>
                        <button
                            @click.stop="$emit('delete', props.article)"
                            class="block w-full cursor-pointer px-4 py-2 text-left text-red-600 hover:bg-red-50 card-action-btn"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 flex flex-1 flex-col">
            <div class="mb-1 text-xs text-slate-500">
                Dipublikasikan: {{ publishedAtText }}
            </div>
            <h2
                class="mb-1 line-clamp-2 text-lg font-semibold"
                :title="isTitleClamped ? props.article.title : ''"
            >
                {{ props.article.title }}
            </h2>
            <Separator />
            <div
                v-html="cleanedContent"
                class="prose mt-2 line-clamp-3 text-sm text-slate-700"
            ></div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
