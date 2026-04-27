<script setup lang="ts">
import { Area, Branch, Regional } from '@/types';
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { createWhatsappLink } from '@/lib/utils';

const selectedRegion = defineModel<{
    regional: Regional | null;
    area: Area | null;
    branch: Branch | null;
} | null>('selected-region', {
    default: null,
});

const getInitials = (name: string) =>
    name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();

watch(
    selectedRegion,
    (newRegion) => {
        if (
            newRegion?.regional &&
            (newRegion.area || newRegion.area == null) &&
            newRegion.branch
        ) {
            isOpen.value = true;
        } else {
            isOpen.value = false;
        }
    },
    {
        deep: true,
    },
);

const closeModal = () => {
    selectedRegion.value = null;
    isOpen.value = false;
};

const stcContacts = computed(
    () => branch.value?.stc_tl_contacts?.filter((c) => c.role === 'STC') ?? [],
);
const tlContacts = computed(
    () => branch.value?.stc_tl_contacts?.filter((c) => c.role === 'TL') ?? [],
);

const regional = computed(() => selectedRegion.value?.regional ?? null);
const area = computed(() => selectedRegion.value?.area ?? null);
const branch = computed(() => selectedRegion.value?.branch ?? null);

const isDirectUnderRegional = computed(() => !branch.value?.area_id);

const isOpen = ref(false);

const handleClickRegion = (
    regional: Regional | null = null,
    area: Area | null = null,
    branch: Branch | null = null,
) => {
    if (selectedRegion.value) {
        selectedRegion.value.regional = regional;
        selectedRegion.value.area = area;
        selectedRegion.value.branch = branch;
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="closeModal">
        <DialogContent
            :aria-describedby="undefined"
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-hidden p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea class="max-h-[calc(100vh-4rem)]">
                <!-- Header -->
                <DialogHeader class="px-6 pt-6 pb-4">
                    <div>
                        <div class="mb-1 flex items-center gap-2">
                            <Badge
                                variant="outline"
                                class="border-violet-200 bg-violet-50 text-[10px] font-medium tracking-wider text-violet-600 uppercase"
                            >
                                Cluster
                            </Badge>
                        </div>
                        <DialogTitle class="text-lg leading-tight font-medium">
                            {{ branch?.name }}
                        </DialogTitle>
                    </div>
                </DialogHeader>

                <Separator />

                <!-- Hierarki -->
                <div class="flex flex-col gap-4 px-6 py-4">
                    <!-- RLQH -->
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50"
                        >
                            <span class="text-[11px] font-medium text-blue-700">
                                {{
                                    getInitials(
                                        regional?.contact_cluster?.name ?? '-',
                                    )
                                }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-0.5 flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="border-blue-200 bg-blue-50 text-[10px] font-medium tracking-wider text-blue-600 uppercase"
                                >
                                    Regional
                                </Badge>
                                <span
                                    @click="
                                        handleClickRegion(regional, null, null)
                                    "
                                    class="truncate text-xs text-muted-foreground transition-colors duration-200 hover:cursor-pointer hover:text-primary"
                                    >{{ regional?.name }}</span
                                >
                            </div>
                            <p class="text-sm leading-tight font-medium">
                                {{ regional?.contact_cluster?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                NIP {{ regional?.contact_cluster?.nip }} ·
                                <a
                                    :href="
                                        createWhatsappLink(
                                            regional?.contact_cluster?.phone ||
                                                null,
                                        )
                                    "
                                    target="_blank"
                                    class="hover:underline"
                                    @click.stop
                                >
                                    {{ regional?.contact_cluster?.phone }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- ALQH — hanya tampil jika ada area -->
                    <div
                        v-if="!isDirectUnderRegional"
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50"
                        >
                            <span
                                class="text-[11px] font-medium text-emerald-700"
                            >
                                {{
                                    getInitials(
                                        area?.contact_cluster?.name ?? '-',
                                    )
                                }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-0.5 flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="border-emerald-200 bg-emerald-50 text-[10px] font-medium tracking-wider text-emerald-600 uppercase"
                                >
                                    Area
                                </Badge>
                                <span
                                    @click="handleClickRegion(regional, area, null)"
                                    class="truncate text-xs text-muted-foreground transition-colors duration-200 hover:text-primary hover:cursor-pointer"
                                    >{{ area?.name }}</span
                                >
                            </div>
                            <p class="text-sm leading-tight font-medium">
                                {{ area?.contact_cluster?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                NIP {{ area?.contact_cluster?.nip }} ·
                                <a
                                    :href="
                                        createWhatsappLink(
                                            area?.contact_cluster?.phone ||
                                                null,
                                        )
                                    "
                                    class="hover:underline"
                                    target="_blank"
                                    @click.stop
                                >
                                    {{ area?.contact_cluster?.phone }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- CLQH -->
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-50"
                        >
                            <span
                                class="text-[11px] font-medium text-violet-700"
                            >
                                {{
                                    getInitials(
                                        branch?.contact_cluster?.name ?? '-',
                                    )
                                }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-0.5 flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="border-violet-200 bg-violet-50 text-[10px] font-medium tracking-wider text-violet-600 uppercase"
                                >
                                    Cluster
                                </Badge>
                                <span
                                    class="truncate text-xs text-muted-foreground"
                                    >{{ branch?.name }}</span
                                >
                            </div>
                            <p class="text-sm leading-tight font-medium">
                                {{ branch?.contact_cluster?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                NIP {{ branch?.contact_cluster?.nip }} ·
                                <a
                                    :href="
                                        createWhatsappLink(
                                            branch?.contact_cluster?.phone ||
                                                null,
                                        )
                                    "
                                    target="_blank"
                                    class="hover:underline"
                                    @click.stop
                                >
                                    {{ branch?.contact_cluster?.phone }}
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- STC & TL -->
                <div class="px-6 py-4">
                    <p
                        class="mb-3 text-[10px] font-medium tracking-widest text-muted-foreground uppercase"
                    >
                        Kontak STC & TL
                    </p>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-0">
                        <!-- STC -->
                        <div>
                            <p
                                class="mb-2 text-[10px] font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                STC ({{ stcContacts.length }})
                            </p>
                            <template v-if="stcContacts.length">
                                <div
                                    v-for="stc in stcContacts"
                                    :key="stc.id"
                                    class="flex items-center gap-2 py-2"
                                >
                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-50"
                                    >
                                        <span
                                            class="text-[9px] font-medium text-amber-700"
                                        >
                                            {{ getInitials(stc.name ?? '-') }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-xs leading-tight font-medium"
                                        >
                                            {{ stc.name }}
                                        </p>
                                        <p
                                            class="truncate text-[11px] text-muted-foreground"
                                        >
                                            <a
                                                class="hover:underline"
                                                :href="
                                                    createWhatsappLink(
                                                        stc.phone || null,
                                                    )
                                                "
                                                target="_blank"
                                            >
                                                {{ stc.phone }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </template>
                            <p
                                v-else
                                class="py-2 text-xs text-muted-foreground"
                            >
                                Tidak ada STC.
                            </p>
                        </div>

                        <!-- Divider vertikal -->
                        <div class="border-l border-border pl-4">
                            <p
                                class="mb-2 text-[10px] font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                TL ({{ tlContacts.length }})
                            </p>
                            <template v-if="tlContacts.length">
                                <div
                                    v-for="tl in tlContacts"
                                    :key="tl.id"
                                    class="flex items-center gap-2 py-2"
                                >
                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-50"
                                    >
                                        <span
                                            class="text-[9px] font-medium text-rose-700"
                                        >
                                            {{ getInitials(tl.name ?? '-') }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-xs leading-tight font-medium"
                                        >
                                            {{ tl.name }}
                                        </p>
                                        <p
                                            class="truncate text-[11px] text-muted-foreground"
                                        >
                                            <a
                                                :href="
                                                    createWhatsappLink(
                                                        tl.phone || null,
                                                    )
                                                "
                                                class="hover:underline"
                                                target="_blank"
                                            >
                                                {{ tl.phone }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </template>
                            <p
                                v-else
                                class="py-2 text-xs text-muted-foreground"
                            >
                                Tidak ada TL.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pb-4" />
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
