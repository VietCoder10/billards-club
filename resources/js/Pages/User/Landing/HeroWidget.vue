<script setup>
import Button from 'primevue/button';
</script>

<template>
  <div id="hero" class="flex flex-col pt-6 px-6 lg:px-20 overflow-hidden relative min-h-[80vh] justify-center items-center text-center mt-6 lg:mt-0">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
      <img src="/images/Thiet-ke-quan-bida.jpg" alt="Billiards" class="w-full h-full object-cover object-center opacity-40" />
      <div class="absolute inset-0 bg-gradient-to-b from-zinc-950/80 via-zinc-950/60 to-zinc-950"></div>
    </div>

    <div class="relative z-10 mx-6 md:mx-20 mt-0 md:mt-6 max-w-4xl">
      <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold text-white leading-tight mb-6 drop-shadow-2xl">
        <span class="font-light block text-xl md:text-3xl tracking-[0.3em] uppercase text-amber-500 mb-4">Trải nghiệm</span>
        Đỉnh Cao Của <br />
        Thể Thao Billiards
      </h1>
      <p class="font-light text-lg md:text-xl leading-relaxed text-zinc-300 mb-10 max-w-2xl mx-auto">Không gian sang trọng, bàn đấu chuẩn quốc tế và dịch vụ đẳng cấp 5 sao dành riêng cho các cơ thủ thực thụ.</p>
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <Button label="Khám Phá" rounded class="!bg-gradient-to-r !from-amber-500 !to-amber-300 !border-none !text-zinc-950 !font-bold !text-lg !px-8 !py-4 uppercase tracking-widest hover:scale-105 transition-all shadow-[0_0_20px_rgba(245,158,11,0.4)]"></Button>
      </div>
    </div>
  </div>
</template>
