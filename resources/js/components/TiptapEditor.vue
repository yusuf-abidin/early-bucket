<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { watch } from 'vue'
import { Bold, Italic, Underline, List, ListOrdered, Undo, Redo } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
    ],
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
})

// Sync jika value berubah dari luar
watch(() => props.modelValue, (val) => {
    if (editor.value && editor.value.getHTML() !== val) {
        editor.value.commands.setContent(val, false)
    }
})
</script>

<template>
    <div class="tiptap-wrapper">
        <!-- Toolbar -->
        <div class="toolbar" v-if="editor">
            <button @click="editor.chain().focus().toggleBold().run()"
                    :class="{ 'is-active': editor.isActive('bold') }"><Bold /></button>
            <button @click="editor.chain().focus().toggleItalic().run()"
                    :class="{ 'is-active': editor.isActive('italic') }"><Italic /></button>
            <button @click="editor.chain().focus().toggleUnderline().run()"
                    :class="{ 'is-active': editor.isActive('underline') }"><Underline /></button>
            <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }">H2</button>
            <button @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }">H3</button>
            <button @click="editor.chain().focus().toggleBulletList().run()"
                    :class="{ 'is-active': editor.isActive('bulletList') }"><List /></button>
            <button @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="{ 'is-active': editor.isActive('orderedList') }"><ListOrdered /></button>
            <button @click="editor.chain().focus().toggleBlockquote().run()"
                    :class="{ 'is-active': editor.isActive('blockquote') }">Quote</button>
            <button @click="editor.chain().focus().undo().run()"><Undo /></button>
            <button @click="editor.chain().focus().redo().run()"><Redo /></button>
        </div>

        <!-- Editor Area -->
        <editor-content :editor="editor" class="editor-content" />
    </div>
</template>

<style scoped>
.tiptap-wrapper {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
}
.toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 8px;
    background: #f9fafb;
    border-bottom: 1px solid #d1d5db;
}
.toolbar button {
    padding: 4px 10px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    font-size: 13px;
}
.toolbar button.is-active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}
.editor-content {
    padding: 16px;
    min-height: 300px;
}
/* Style untuk konten di dalam editor */
.editor-content :deep(.ProseMirror) {
    outline: none;
    min-height: 280px;
}
.editor-content :deep(.ProseMirror h2) { font-size: 1.5rem; font-weight: bold; }
.editor-content :deep(.ProseMirror h3) { font-size: 1.25rem; font-weight: bold; }
.editor-content :deep(.ProseMirror ul) { list-style: disc; padding-left: 1.5rem; }
.editor-content :deep(.ProseMirror ol) { list-style: decimal; padding-left: 1.5rem; }
.editor-content :deep(.ProseMirror blockquote) {
    border-left: 4px solid #d1d5db;
    padding-left: 1rem;
    color: #6b7280;
}
</style>
