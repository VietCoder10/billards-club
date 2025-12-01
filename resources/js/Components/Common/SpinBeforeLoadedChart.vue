<script setup>
import { useRequestStore } from '@/store/request'
import { watch } from 'vue'
const requestStore = useRequestStore()
watch(
  () => requestStore.getIsFetcing.length,
  (newValue) => (document.body.style.overflowY = newValue ? 'hidden' : '')
)
</script>

<template>
  <div class="lds-ellipsis loading-chart">
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
    return {}
  },
  methods: {},
}
</script>
<style scoped>
.loading-chart {
  position: absolute;
  top: -50px;
  right: 0;
  bottom: 0;
  left: -50px;
  z-index: 999999;
  width: 2em;
  height: 2em;
  margin: auto;
  overflow: show;
  @include mq-down(md) {
    left: -3rem;
  }
}
</style>
