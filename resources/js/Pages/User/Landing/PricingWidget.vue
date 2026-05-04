<script setup>
import Button from 'primevue/button';
</script>
<template>
  <div id="pricing" class="py-20 px-6 lg:px-20 mx-0 lg:mx-20">
    <div class="text-center mb-16">
      <h2 class="text-amber-500 font-bold tracking-widest uppercase mb-2">Bảng Giá</h2>
      <h3 class="text-4xl md:text-5xl font-serif font-bold text-white">Lựa Chọn Phù Hợp Cho Mọi Nhu Cầu</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
      <!-- Standard -->
      <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 hover:border-amber-500/50 transition-all flex flex-col">
        <h4 class="text-2xl font-bold text-white mb-2">Bàn tiêu chuẩn</h4>
        <p class="text-zinc-400 mb-6">Bàn khu vực chung</p>
        <div class="text-4xl font-bold text-white mb-8">80k <span class="text-lg text-zinc-500 font-normal">/giờ</span></div>
        <ul class="space-y-4 text-zinc-300 mb-8 flex-1">
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Bàn tiêu chuẩn quốc tế</span></li>
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Cơ tiêu chuẩn CLB</span></li>
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Phục vụ đồ uống cơ bản</span></li>
        </ul>
      </div>

      <!-- VIP -->
      <div class="bg-gradient-to-b from-zinc-800 to-zinc-900 border border-amber-500 rounded-3xl p-8 transform md:-translate-y-4 shadow-[0_0_30px_rgba(245,158,11,0.15)] flex flex-col relative overflow-hidden">
        <div class="absolute top-0 right-0 bg-amber-500 text-zinc-950 text-xs font-bold px-3 py-1 rounded-bl-lg uppercase tracking-wider">Phổ biến nhất</div>
        <h4 class="text-2xl font-bold text-amber-500 mb-2">Bàn VIP</h4>
        <p class="text-zinc-400 mb-6">Sử dụng bàn Rasson</p>
        <div class="text-4xl font-bold text-white mb-8">150k <span class="text-lg text-zinc-500 font-normal">/ giờ</span></div>
        <ul class="space-y-4 text-zinc-300 mb-8 flex-1">
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Phòng riêng cách âm</span></li>
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Sofa da & Tivi màn hình lớn</span></li>
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Nhân viên phục vụ riêng</span></li>
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Cơ ngọn mộc cao cấp</span></li>
        </ul>
      </div>

      <!-- Tournament -->
      <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 hover:border-amber-500/50 transition-all flex flex-col">
        <h4 class="text-2xl font-bold text-white mb-2">Bàn thi đấu</h4>
        <p class="text-zinc-400 mb-6">Không gian thi đấu chuyên nghiệp</p>
        <div class="text-4xl font-bold text-white mb-8">250k <span class="text-lg text-zinc-500 font-normal">/ giờ</span></div>
        <ul class="space-y-4 text-zinc-300 mb-8 flex-1">
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Phòng Super VIP lớn nhất</span></li>
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Minibar miễn phí</span></li>
          <li class="flex items-center gap-3"><i class="pi pi-check text-amber-500"></i><span>Tủ cigar & Rượu vang</span></li>
        </ul>
      </div>
    </div>
  </div>
</template>
