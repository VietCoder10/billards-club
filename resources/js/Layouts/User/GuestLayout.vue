<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';

const toast = useToast();

const displayFlash = () => {
  const flash = usePage().props.value.flash;
  if (flash && flash.success) {
    toast.add({ severity: 'success', summary: 'Thành công', detail: flash.success, life: 3000 });
  }
  if (flash && flash.error) {
    toast.add({ severity: 'error', summary: 'Lỗi', detail: flash.error, life: 3000 });
  }
};

onMounted(() => {
  displayFlash();
});

watch(
  () => usePage().props.value.flash,
  () => {
    displayFlash();
  },
  { deep: true }
);
</script>

<template>
  <div class="bg-zinc-950 text-white min-h-screen font-sans selection:bg-amber-500 selection:text-zinc-950 flex items-center justify-center relative overflow-hidden">
    <Toast />
    <!-- Background Image -->
    <div class="absolute inset-0 z-0">
      <img src="/images/thiet-ke-quan-bida.jpg" alt="Background" class="w-full h-full object-cover object-center opacity-30" />
      <div class="absolute inset-0 bg-gradient-to-b from-zinc-950/80 via-zinc-950/90 to-zinc-950"></div>
    </div>

    <div class="relative z-10 w-full max-w-md px-6 py-12 md:p-10 bg-zinc-900/60 backdrop-blur-xl border border-zinc-800 rounded-3xl shadow-2xl m-4">
      <div class="text-center mb-10">
        <Link href="/" class="inline-block">
          <span class="text-white font-bold text-3xl tracking-widest uppercase"> Việt Vũ <span class="text-amber-500">Billiards</span> </span>
        </Link>
      </div>

      <slot />
    </div>
  </div>
</template>
