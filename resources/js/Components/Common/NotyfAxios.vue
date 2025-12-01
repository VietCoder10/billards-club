<template>
  <Toast />
</template>
<script setup>
import { useToast } from 'primevue/usetoast';
import { reactive, watch } from 'vue';
import { useToastStore } from '@/store/toast';
const toastStore = useToastStore();
const toast = useToast();
watch(
  toastStore,
  function (val) {
    if (val.getToast.message) {
      toast.add({
        severity: val.getToast.mode,
        detail: val.getToast.message,
        life: 3000
      });
      toastStore.setToast('', 'error');
    }
  },
  {
    immediate: true,
    deep: true
  }
);
</script>
