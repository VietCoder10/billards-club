<script setup>
const props = defineProps({
  tablePrices: {
    type: Array,
    default: () => []
  }
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price) + 'k';
};
</script>

<template>
  <div id="pricing" class="py-20 px-6 lg:px-20 mx-0 lg:mx-20">
    <div class="text-center mb-16">
      <h2 class="text-amber-500 font-bold tracking-widest uppercase mb-2">Bảng Giá</h2>
      <h3 class="text-4xl md:text-5xl font-bold text-white">Lựa Chọn Phù Hợp Cho Mọi Nhu Cầu</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
      <div
        v-for="(price, index) in tablePrices.filter(p => p !== null && p !== undefined)"
        :key="price.id"
        :class="[
          'rounded-3xl p-8 flex flex-col transition-all',
          index === 1
            ? 'bg-gradient-to-b from-zinc-800 to-zinc-900 border border-amber-500 transform md:-translate-y-4 shadow-[0_0_30px_rgba(245,158,11,0.15)] relative overflow-hidden'
            : 'bg-zinc-900 border border-zinc-800 hover:border-amber-500/50'
        ]"
      >
        <!-- Nhãn "Phổ biến nhất" cho mục thứ 2 -->
        <div
          v-if="index === 1"
          class="absolute top-0 right-0 bg-amber-500 text-zinc-950 text-xs font-bold px-3 py-1 rounded-bl-lg uppercase tracking-wider"
        >
          Phổ biến nhất
        </div>

        <h4 :class="['text-2xl font-bold mb-2', index === 1 ? 'text-amber-500' : 'text-white']">
          {{ price.price_name }}
        </h4>
        <p class="text-zinc-400 mb-6">Giá theo giờ sử dụng</p>

        <div class="text-4xl font-bold text-white mb-8">
          {{ new Intl.NumberFormat('vi-VN').format(price.price_per_hour) }}
          <span class="text-lg text-zinc-500 font-normal"> VNĐ/giờ</span>
        </div>

        <ul class="space-y-4 text-zinc-300 mb-8 flex-1">
          <li class="flex items-center gap-3">
            <i class="pi pi-check text-amber-500"></i>
            <span>Bàn tiêu chuẩn chất lượng cao</span>
          </li>
          <li class="flex items-center gap-3">
            <i class="pi pi-check text-amber-500"></i>
            <span>Cơ &amp; dụng cụ đầy đủ</span>
          </li>
          <li class="flex items-center gap-3">
            <i class="pi pi-check text-amber-500"></i>
            <span>Phục vụ đồ uống</span>
          </li>
          <li v-if="index >= 1" class="flex items-center gap-3">
            <i class="pi pi-check text-amber-500"></i>
            <span>Không gian riêng tư</span>
          </li>
          <li v-if="index >= 2" class="flex items-center gap-3">
            <i class="pi pi-check text-amber-500"></i>
            <span>Dịch vụ VIP cao cấp</span>
          </li>
        </ul>
      </div>

      <!-- Fallback nếu không có dữ liệu -->
      <div v-if="tablePrices.length === 0" class="col-span-3 text-center text-zinc-400 py-12">
        <i class="pi pi-info-circle text-4xl mb-4 block text-amber-500"></i>
        <p>Bảng giá đang được cập nhật. Vui lòng liên hệ để biết thêm chi tiết.</p>
      </div>
    </div>
  </div>
</template>

