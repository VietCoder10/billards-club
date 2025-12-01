<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import { watch } from 'vue';
import { useToast } from 'primevue/usetoast';
const home = ref({
  icon: 'pi pi-home'
});
const page = usePage();
const toast = useToast();

const model = ref([]);
watch(
  page.props,
  function (val) {
    if (val.dataSession) {
      setTimeout(() => {
        toast.add({
          severity: val.dataSession.mode ?? '',
          summary: val.dataSession.message,
          life: 3000
        });
      }, 100);
    }
  },
  {
    immediate: true,
    deep: true
  }
);
</script>
<template>
  <Toast />
</template>
