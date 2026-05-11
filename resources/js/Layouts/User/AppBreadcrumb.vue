<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import { watch } from 'vue';
const home = ref({
  icon: 'pi pi-home'
});
const page = usePage();

const model = ref([]);
watch(
  page.props,
  function (val) {
    model.value = val.breadcrumbs;
  },
  {
    immediate: true,
    deep: true
  }
);
</script>

<template>
  <Breadcrumb class="mb-2" :model="model">
    <template #item="{ item }">
      <Link class="cursor-pointer" :href="item.url">
        <span :class="item.icon" style="color: var(--p-primary-color)"></span>
        <span class="text-primary font-semibold ml-2">{{ item.label }}</span>
      </Link>
    </template>
  </Breadcrumb>
</template>
<style scoped>
.p-breadcrumb {
  padding: 0px !important;
}
.p-divider {
  margin: 0px !important;
}
</style>
