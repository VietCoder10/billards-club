<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';

const props = defineProps({
  data: Object,
});
</script>

<template>
  <UserLayout>
    <template #content>
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-surface-900 dark:text-white">{{ data.title }}</h1>
          <p class="text-surface-500 text-sm mt-1">
            Danh sách bàn đang trống — sẵn sàng phục vụ
          </p>
        </div>
        <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-sm font-semibold px-4 py-2 rounded-full">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
          {{ data.tables.length }} bàn trống
        </div>
      </div>

      <!-- Grid bàn trống -->
      <div v-if="data.tables.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
        <div
          v-for="table in data.tables"
          :key="table.id"
          class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-5 flex flex-col gap-3 transition-all duration-300 hover:border-emerald-500/60 hover:shadow-md hover:shadow-emerald-900/20"
        >
          <!-- Icon + Tên -->
          <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
              <i class="pi pi-check-circle text-emerald-400 text-lg"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded-full">
              Trống
            </span>
          </div>

          <div>
            <p class="font-bold text-base text-surface-900 dark:text-white leading-tight">{{ table.table_name }}</p>
            <p v-if="table.price_name" class="text-xs text-surface-500 mt-0.5">{{ table.price_name }}</p>
          </div>

          <!-- Giá -->
          <div v-if="table.price_per_hour" class="mt-auto pt-3 border-t border-emerald-500/20">
            <p class="text-xs text-surface-500">Giá theo giờ</p>
            <p class="text-sm font-bold text-emerald-400">
              {{ new Intl.NumberFormat('vi-VN').format(table.price_per_hour) }} VNĐ
            </p>
          </div>
        </div>
      </div>

      <!-- Không có bàn trống -->
      <div v-else class="flex flex-col items-center justify-center py-24 text-surface-400 gap-4">
        <div class="w-20 h-20 rounded-full bg-surface-100 dark:bg-surface-800 flex items-center justify-center">
          <i class="pi pi-times-circle text-4xl text-red-400"></i>
        </div>
        <p class="text-lg font-semibold text-surface-700 dark:text-surface-200">Hiện không có bàn trống</p>
        <p class="text-sm text-surface-500">Vui lòng quay lại sau hoặc liên hệ nhân viên để được hỗ trợ</p>
      </div>
    </template>
  </UserLayout>
</template>
