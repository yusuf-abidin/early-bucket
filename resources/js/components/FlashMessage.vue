<script setup lang="ts">

import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Toaster } from './ui/sonner';
import { onMounted } from 'vue';

const page = usePage();

onMounted(() => {
  if (page.props.flash?.success) {
    toast.success(page.props.flash.success)
  }
})
// if(flash.success) {
//     console.log(flash)
//     toast('Event has been created', {
//         description: 'Sunday, December 03, 2023 at 9:00 AM',
//         action: {
//           label: 'Undo',
//           onClick: () => console.log('Undo'),
//         },
//       })
// }
</script>

<template>
    <Toaster/>
</template>

<style scoped></style>
