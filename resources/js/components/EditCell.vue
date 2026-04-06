<script setup lang="ts">

const emit = defineEmits<{
    (e: 'edit') : void
}>()

const handleClick = () => emit('edit')

</script>

<template>
    <div class="flex items-center justify-center">
        <button
            type="button"
            @click="handleClick"
            class="flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground transition-all visible md:invisible group-hover:visible hover:bg-primary/10 hover:text-primary active:scale-95"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke="currentColor"
                class="h-3.5 w-3.5"
            >
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
        </button>
    </div>
</template>

<style scoped></style>
