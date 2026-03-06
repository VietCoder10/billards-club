<script setup>
import { computed } from 'vue';

const props = defineProps({
  table: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['click']);

const statusColor = computed(() => {
  switch (props.table.status) {
    case 2:
      return 'bg-green-500 hover:bg-green-600'; // Đang chơi
    case 3:
      return 'bg-yellow-500 hover:bg-yellow-600'; // Bảo trì
    default:
      return 'bg-red-500 hover:bg-red-600'; // Đang bàn trống
  }
  500;
});

const statusText = computed(() => {
  switch (props.table.status) {
    case 2:
      return 'Đang chơi';
    case 3:
      return 'Bàn bảo trì';
    default:
      return 'Bàn trống';
  }
});
</script>

<template>
  <div @click="emit('click', table)" :class="['aspect-square rounded-xl shadow-lg cursor-pointer transition-all duration-300 transform hover:scale-105 flex flex-col items-center justify-center text-white p-4', statusColor]">
    <div class="text-4xl mb-2">
      <i class="pi pi-money-bill">{{ table.price_per_hour }}</i>
    </div>
    <div class="text-xl font-bold uppercase">{{ table.table_name }}</div>
    <div class="text-sm opacity-90 mt-1 font-medium">{{ statusText }}</div>
    <div v-if="table.status === 1" class="mt-2 flex items-center gap-1 text-xs">
      <i class="pi pi-clock"></i>
      <span>{{ table.playing_time || '00:00' }}</span>
    </div>
  </div>
</template>

<style scoped>
.aspect-square {
  aspect-ratio: 1 / 1;
}
</style>
