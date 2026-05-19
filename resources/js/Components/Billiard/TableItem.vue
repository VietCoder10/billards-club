<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  table: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['click']);

const elapsed = ref('00:00');
let timerId = null;

const calculateElapsedTime = () => {
  if (props.table.status === 2 && props.table.started_at) {
    try {
      // Convert standard SQL datetime string (YYYY-MM-DD HH:mm:ss) into ISO compatible string
      const startStr = props.table.started_at.replace(' ', 'T');
      const start = new Date(startStr);
      const now = new Date();
      const diffMs = now.getTime() - start.getTime();
      
      if (diffMs > 0) {
        const totalMinutes = Math.floor(diffMs / 60000);
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        
        const formattedHours = hours.toString().padStart(2, '0');
        const formattedMinutes = minutes.toString().padStart(2, '0');
        
        elapsed.value = `${formattedHours}:${formattedMinutes}`;
      } else {
        elapsed.value = '00:00';
      }
    } catch (e) {
      elapsed.value = '00:00';
    }
  } else {
    elapsed.value = '00:00';
  }
};

onMounted(() => {
  calculateElapsedTime();
  // Update every 10 seconds for real-time accuracy
  timerId = setInterval(calculateElapsedTime, 10000);
});

onUnmounted(() => {
  if (timerId) clearInterval(timerId);
});

const statusText = computed(() => {
  switch (props.table.status) {
    case 2:
      return 'Đang chơi';
    case 3:
      return 'Bảo trì';
    default:
      return 'Bàn trống';
  }
});
</script>

<template>
  <div 
    @click="emit('click', table)" 
    :class="[
      'relative group overflow-hidden rounded-2xl border bg-white dark:bg-zinc-900 p-5 flex flex-col justify-between transition-all duration-300 cursor-pointer shadow-sm hover:shadow-xl hover:-translate-y-1 select-none',
      table.status === 2 
        ? 'border-emerald-150 dark:border-emerald-950/40 shadow-emerald-50/20 hover:border-emerald-400 dark:hover:border-emerald-500' 
        : table.status === 3
          ? 'border-amber-150 dark:border-amber-950/40 hover:border-amber-400 dark:hover:border-amber-500'
          : 'border-rose-150 dark:border-rose-950/40 shadow-rose-50/20 hover:border-rose-450 dark:hover:border-rose-500'
    ]"
  >
    <!-- Background interactive glows -->
    <div 
      v-if="table.status === 2" 
      class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 dark:from-emerald-900/10 dark:to-teal-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
    ></div>
    <div 
      v-else-if="table.status === 1" 
      class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-red-500/5 dark:from-rose-900/10 dark:to-red-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
    ></div>

    <!-- Header Section -->
    <div class="relative z-10 flex items-center justify-between mb-4">
      <span class="text-[9px] font-black text-zinc-400 dark:text-zinc-500 tracking-widest uppercase">
        {{ table.price_name || 'BÀN TIÊU CHUẨN' }}
      </span>
      
      <!-- Status Badge -->
      <span 
        :class="[
          'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border flex items-center gap-1.5 shadow-sm',
          table.status === 2
            ? 'bg-emerald-50/80 text-emerald-750 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/40'
            : table.status === 3
              ? 'bg-amber-50/80 text-amber-750 border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/40'
              : 'bg-rose-50/80 text-rose-750 border-rose-200/60 dark:bg-rose-950/40 dark:text-rose-450 dark:border-rose-900/40'
        ]"
      >
        <span 
          :class="[
            'w-1.5 h-1.5 rounded-full',
            table.status === 2 ? 'bg-emerald-500 animate-pulse' : table.status === 3 ? 'bg-amber-500' : 'bg-rose-500'
          ]"
        ></span>
        {{ statusText }}
      </span>
    </div>

    <!-- Center Section: Custom Graphic and Details -->
    <div class="relative z-10 flex flex-col items-center text-center my-3">
      <!-- Round Badge Icon with Shadow -->
      <div 
        :class="[
          'w-14 h-14 rounded-2xl flex items-center justify-center mb-3 transition-transform duration-300 group-hover:scale-110 shadow-inner',
          table.status === 2
            ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-650 dark:text-emerald-400'
            : table.status === 3
              ? 'bg-amber-50 dark:bg-amber-950/30 text-amber-650 dark:text-amber-400'
              : 'bg-rose-50 dark:bg-rose-950/30 text-rose-650 dark:text-rose-450'
        ]"
      >
        <i 
          :class="[
            'pi text-xl',
            table.status === 2 ? 'pi-play-circle text-emerald-500' : table.status === 3 ? 'pi-wrench text-amber-500' : 'pi-check-circle text-rose-500'
          ]"
        ></i>
      </div>
      
      <!-- Table Name -->
      <h4 
        :class="[
          'font-black text-lg md:text-xl tracking-tight leading-tight uppercase transition-colors',
          table.status === 2
            ? 'text-emerald-700 dark:text-emerald-400 group-hover:text-emerald-600'
            : table.status === 3
              ? 'text-amber-700 dark:text-amber-400 group-hover:text-amber-600'
              : 'text-rose-700 dark:text-rose-450 group-hover:text-rose-600'
        ]"
      >
        {{ table.table_name }}
      </h4>
      
      <!-- Time status / helper indicator -->
      <div class="mt-2.5">
        <div 
          v-if="table.status === 2"
          class="inline-flex items-center gap-1.5 text-xs text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/20 px-2.5 py-0.5 rounded-lg border border-emerald-100 dark:border-emerald-950/40"
        >
          <i class="pi pi-clock text-[10px] animate-spin"></i>
          <span>{{ elapsed }}</span>
        </div>
        <div 
          v-else-if="table.status === 1"
          class="inline-flex items-center gap-1.5 text-[11px] text-rose-600 dark:text-rose-450 font-medium"
        >
          <i class="pi pi-play text-[9px]"></i>
          <span>Mở bàn trống</span>
        </div>
        <div 
          v-else
          class="inline-flex items-center gap-1.5 text-[11px] text-amber-650 dark:text-amber-500 font-semibold"
        >
          <i class="pi pi-exclamation-circle text-[10px]"></i>
          <span>Tạm ngưng phục vụ</span>
        </div>
      </div>
    </div>

    <!-- Footer Section -->
    <div 
      :class="[
        'relative z-10 mt-5 pt-3 border-t flex items-center justify-between text-xs',
        table.status === 2
          ? 'border-emerald-100 dark:border-emerald-950/30'
          : table.status === 3
            ? 'border-amber-100 dark:border-amber-950/30'
            : 'border-rose-100 dark:border-rose-950/30'
      ]"
    >
      <span class="text-zinc-400 dark:text-zinc-500 font-medium">Giá theo giờ</span>
      <span 
        :class="[
          'font-black text-sm tracking-tight',
          table.status === 2
            ? 'text-emerald-600 dark:text-emerald-400'
            : table.status === 3
              ? 'text-amber-600 dark:text-amber-400'
              : 'text-rose-600 dark:text-rose-450'
        ]"
      >
        {{ new Intl.NumberFormat('vi-VN').format(table.price_per_hour) }}đ/h
      </span>
    </div>
  </div>
</template>

<style scoped>
/* Keeping aspect ratio fluid but highly optimized for rectangular cards */
.aspect-square {
  aspect-ratio: 1 / 1;
}
</style>
