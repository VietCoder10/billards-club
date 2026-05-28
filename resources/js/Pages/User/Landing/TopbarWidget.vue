<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import Button from 'primevue/button';

function smoothScroll(id) {
  document.body.click();
  const element = document.getElementById(id);
  if (element) {
    element.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
  }
}
</script>

<template>
  <a class="flex items-center" href="#">
    <svg viewBox="0 0 54 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-10 mr-3">
      <!-- Simple Gold Triangle/Diamond Logo -->
      <path d="M27 0L54 20L27 40L0 20L27 0Z" fill="url(#gold-grad)" />
      <defs>
        <linearGradient id="gold-grad" x1="0" y1="0" x2="54" y2="40" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FDE68A" />
          <stop offset="1" stop-color="#D97706" />
        </linearGradient>
      </defs>
    </svg>
    <span class="text-white font-serif font-bold text-2xl tracking-widest uppercase mr-20"> TQ <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-amber-500">Billiards</span> </span>
  </a>
  <Button class="lg:!hidden" text severity="secondary" rounded v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }">
    <i class="pi pi-bars !text-2xl text-amber-500"></i>
  </Button>
  <div
    class="items-center bg-zinc-900/95 backdrop-blur-md lg:bg-transparent lg:backdrop-blur-none grow justify-between hidden lg:flex absolute lg:static w-full left-0 top-full px-12 lg:px-0 z-20 rounded-xl border border-zinc-800 lg:border-none py-4 lg:py-0 shadow-2xl lg:shadow-none"
  >
    <ul class="list-none p-0 m-0 flex lg:items-center select-none flex-col lg:flex-row cursor-pointer gap-8">
      <li>
        <a @click="smoothScroll('home')" class="px-0 py-2 text-zinc-300 hover:text-amber-500 transition-colors font-medium text-lg uppercase tracking-wide">
          <span>Trang chủ</span>
        </a>
      </li>
      <!-- <li>
        <a @click="smoothScroll('features')" class="px-0 py-2 text-zinc-300 hover:text-amber-500 transition-colors font-medium text-lg uppercase tracking-wide">
          <span>Không gian</span>
        </a>
      </li> -->
      <li>
        <a @click="smoothScroll('highlights')" class="px-0 py-2 text-zinc-300 hover:text-amber-500 transition-colors font-medium text-lg uppercase tracking-wide">
          <span>Dịch vụ</span>
        </a>
      </li>
      <li>
        <a @click="smoothScroll('pricing')" class="px-0 py-2 text-zinc-300 hover:text-amber-500 transition-colors font-medium text-lg uppercase tracking-wide">
          <span>Bảng giá</span>
        </a>
      </li>
    </ul>
    <div class="flex border-t border-zinc-800 lg:border-t-0 py-4 lg:py-0 mt-4 lg:mt-0 gap-4">
      <Link :href="route('user.login.index')">
        <Button label="Đăng nhập" text class="!text-zinc-300 hover:!text-amber-500 font-serif tracking-widest" rounded></Button>
      </Link>
      <Link :href="route('user.register.index')">
        <Button label="Đăng ký" text class="!bg-gradient-to-r !from-amber-600 !to-amber-400 !border-none !text-zinc-950 font-bold uppercase tracking-widest hover:scale-105 transition-transform" rounded></Button>
      </Link>
    </div>
  </div>
</template>
