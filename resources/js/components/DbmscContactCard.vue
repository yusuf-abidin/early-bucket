<script setup lang="ts">
import { BranchContact, DbmscContact } from '@/types';
import { computed, ref } from 'vue';
import { createWhatsappLink } from '@/lib/utils';

const props = defineProps<{
    branch_contact: BranchContact;
    contact: DbmscContact;
}>();

const initials = computed(
    () =>
        props.contact.name
            ?.split(' ')
            .slice(0, 2)
            .map((n) => n[0])
            .join('')
            .toUpperCase() ?? '?',
);

const emit = defineEmits<{
    (e: 'edit', contact: DbmscContact, branchContact: BranchContact): void;
}>();

const copied = ref(false);

async function copyPhone() {
    try {
        await navigator.clipboard.writeText(props.contact.phone);
        copied.value = true;
        setTimeout(() => (copied.value = false), 1800);
    } catch {
        console.log('Gagal menyalin nomor');
    }
}
</script>

<template>
    <div
        class="group relative box-border flex h-full w-full cursor-default items-start gap-3 overflow-hidden rounded-xl border border-slate-200 bg-white p-3 pl-4 transition-all hover:border-emerald-200 hover:shadow-sm"
    >
        <div
            class="absolute top-0 bottom-0 left-0 w-1.5 rounded-l bg-emerald-500"
        ></div>

        <button
            @click="emit('edit', contact, props.branch_contact)"
            class="absolute top-1.5 right-1.5 cursor-pointer rounded-full border border-slate-200 bg-white p-1.5 text-slate-400 opacity-0 transition-all group-hover:opacity-100 hover:bg-emerald-50 hover:text-emerald-600 shadow-sm"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
            >
                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                <path d="m15 5 4 4" />
            </svg>
        </button>

        <img
            v-if="contact.avatar"
            :src="`/storage/${contact.avatar}`"
            :alt="contact.name"
            class="mt-0.5 h-20 w-20 shrink-0 rounded-full border border-slate-100 object-cover shadow-sm"
        />

        <div
            v-else
            class="mt-0.5 flex h-20 w-20 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 shadow-inner"
        >
            <span class="text-2xl font-bold text-slate-400">
                {{ initials }}
            </span>
        </div>

        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <span
                class="inline-block w-fit rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold tracking-wider text-emerald-800 uppercase"
            >
                DBMSC
            </span>

            <span
                :title="contact.name ? contact.name : '-'"
                class="truncate text-sm font-semibold text-slate-700"
            >
                {{ contact.name }}
            </span>

            <div class="flex items-center gap-1">
                <a
                    :href="createWhatsappLink(contact.phone ?? null)"
                    target="_blank"
                    class="flex min-w-0 items-center gap-1 text-slate-600 no-underline transition-colors hover:text-emerald-600"
                >
                    <svg
                        class="h-3.5 w-3.5 shrink-0 text-emerald-500"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
                        />
                    </svg>
                    <span
                        v-if="contact.phone"
                        class="truncate text-sm font-medium"
                        >{{ contact.phone }}</span
                    >
                    <span v-else class="truncate text-sm font-medium">-</span>
                </a>

                <button
                    @click="copyPhone"
                    v-if="contact.phone"
                    class="shrink-0 rounded p-1 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600"
                    :aria-label="copied ? 'Tersalin!' : 'Salin nomor'"
                >
                    <!-- Icon check saat copied -->
                    <svg
                        v-if="copied"
                        class="h-3.5 w-3.5 text-emerald-500"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <svg
                        v-else
                        class="h-3.5 w-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <rect
                            x="9"
                            y="9"
                            width="13"
                            height="13"
                            rx="2"
                            ry="2"
                        />
                        <path
                            d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"
                        />
                    </svg>
                </button>
            </div>
            <span class="text-[11px] font-medium tracking-wide text-slate-500">
                NIP: {{ contact.nip ? contact.nip : '-' }}
            </span>
        </div>
    </div>
</template>

<style scoped></style>
