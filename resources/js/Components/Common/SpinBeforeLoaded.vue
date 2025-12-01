<script setup>
import { useRequestStore } from '@/store/request';
import { watch } from 'vue';
const requestStore = useRequestStore();
</script>

<template>
  <div class="lds-ellipsis loading" v-if="requestStore.isLoading">
    <div></div>
    <div></div>
    <div></div>
    <div></div>
  </div>
</template>

<script>
export default {
  props: {},
  data() {
    return {};
  },
  methods: {}
};
</script>
