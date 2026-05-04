<script setup>
import Button from 'primevue/button';
</script>
<template>
  <div id="highlights" class="py-20 px-6 lg:px-20 mx-0 bg-zinc-900/50 border-y border-zinc-800/50">
    <div class="flex flex-col lg:flex-row items-center gap-16 max-w-7xl mx-auto">
      <div class="flex-1">
        <div class="relative">
          <div class="absolute -inset-4 bg-gradient-to-r from-amber-500 to-amber-700 blur-lg opacity-30 rounded-3xl"></div>
          <img src="/images/peri.jpeg" alt="VIP Room" class="relative rounded-3xl w-full object-cover shadow-2xl h-[500px]" />
        </div>
      </div>
      <div class="flex-1 space-y-8">
        <h2 class="text-amber-500 font-bold tracking-widest uppercase">Trải Nghiệm Thượng Lưu</h2>
        <h3 class="text-4xl md:text-5xl font-serif font-bold text-white leading-tight">Dịch Vụ Chăm Sóc Khách Hàng Hoàn Hảo</h3>
        <p class="text-zinc-300 text-lg leading-relaxed">
          Chúng tôi không chỉ cung cấp không gian chơi bida, chúng tôi mang đến một trải nghiệm giải trí toàn diện. Đội ngũ nhân viên chuyên nghiệp, tận tâm luôn sẵn sàng phục vụ từng yêu cầu nhỏ nhất của bạn.
        </p>
        <ul class="space-y-4 text-zinc-300">
          <li class="flex items-center gap-3">
            <i class="pi pi-check-circle text-amber-500 text-xl"></i>
            <span>Nhân viên xếp bi chuyên nghiệp</span>
          </li>
          <li class="flex items-center gap-3">
            <i class="pi pi-check-circle text-amber-500 text-xl"></i>
            <span>Hệ thống điều hòa không khí lọc bụi</span>
          </li>
          <li class="flex items-center gap-3">
            <i class="pi pi-check-circle text-amber-500 text-xl"></i>
            <span>Bãi đỗ xe an toàn, rộng rãi</span>
          </li>
        </ul>
        <!-- <div class="pt-4">
          <Button label="Xem thêm dịch vụ" outlined class="!text-amber-500 !border-amber-500 hover:!bg-amber-500 hover:!text-zinc-950 transition-all font-bold px-6 py-3"></Button>
        </div> -->
      </div>
    </div>
  </div>
</template>
