<script setup>
import { Link } from '@inertiajs/inertia-vue3';
</script>
<template>
  <th class="p-datatable-header-cell" v-for="(item, index) in data" :key="index">
    <div class="p-datatable-column-header-content">
      <Link class="sort-link" :href="item.link"
        ><span class="p-datatable-column-title">{{ item.name }}</span>
        <i class="pi" :class="item.iconDirection"></i>
      </Link>
    </div>
  </th>
</template>

<script>
export default {
  components: {},
  props: ['data'],
  mounted() {},
  methods: {}
};
</script>
