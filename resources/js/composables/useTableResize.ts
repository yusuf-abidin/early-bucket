import { onMounted, onUnmounted, ref } from 'vue';

interface UseTableResizeOptions {
    storageKey: string;
    defaultWidths: Record<string, number>;
    minWidth?: number;
}

export function useTableResize(options: UseTableResizeOptions) {
    const { storageKey, defaultWidths, minWidth = 80 } = options;

    const columnWidths = ref<Record<string, number>>({ ...defaultWidths });
    const resizing = ref<string | null>(null);
    const startX = ref(0);
    const startWidth = ref(0);

    // Load saved widths from localStorage
    const loadColumnWidths = () => {
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                columnWidths.value = { ...defaultWidths, ...parsed };
            }
        } catch (e) {
            console.error('Failed to load column widths:', e);
        }
    };

    // Save widths to localStorage
    const saveColumnWidths = () => {
        try {
            localStorage.setItem(
                storageKey,
                JSON.stringify(columnWidths.value),
            );
        } catch (e) {
            console.error('Failed to save column widths:', e);
        }
    };

    // Start resizing
    const startResize = (e: MouseEvent, column: string) => {
        e.preventDefault();
        resizing.value = column;
        startX.value = e.clientX;
        startWidth.value = columnWidths.value[column] || defaultWidths[column];
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    };

    // Handle mouse move during resize
    const handleMouseMove = (e: MouseEvent) => {
        if (!resizing.value) return;

        const diff = e.clientX - startX.value;
        const newWidth = Math.max(minWidth, startWidth.value + diff);
        columnWidths.value[resizing.value] = newWidth;
    };

    // Stop resizing
    const stopResize = () => {
        if (resizing.value) {
            saveColumnWidths();
            resizing.value = null;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        }
    };

    // Setup and cleanup
    onMounted(() => {
        loadColumnWidths();
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', stopResize);
    });

    onUnmounted(() => {
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', stopResize);
    });

    return {
        columnWidths,
        resizing,
        startResize,
    };
}
