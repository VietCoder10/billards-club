<script setup>
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
import { usePage } from '@inertiajs/inertia-vue3';
import { watch } from 'vue';
const page = usePage();

const model = ref([
  {
    items: []
  }
]);
watch(
  page.props,
  function (val) {
    model.value[0].items = val.leftMenu;
  },
  {
    immediate: true,
    deep: true
  }
);
</script>

<template>
  <ul class="layout-menu">
    <template v-for="(item, i) in model" :key="item">
      <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
      <li v-if="item.separator" class="menu-separator"></li>
    </template>
  </ul>
</template>

<style lang="scss" scoped></style>
