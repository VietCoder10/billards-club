<template>
  <Toast />
</template>
<script setup>
import { usePage } from '@inertiajs/inertia-vue3';
import { watch } from 'vue';
import { useToast } from 'primevue/usetoast';
const toast = useToast();
const page = usePage();
watch(
  page.props,
  function (val) {
    if (val.errors && Object.values(val.errors).length) {
      toast.add({
        severity: 'error',
        detail: Object.values(val.errors)[0],
        life: 3000
      });
    }
  },
  {
    immediate: true,
    deep: true
  }
);
</script>
