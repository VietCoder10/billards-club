<script setup>
import FeaturesWidget from '@/Pages/User/Landing/FeaturesWidget.vue';
import FooterWidget from '@/Pages/User/Landing/FooterWidget.vue';
import HeroWidget from '@/Pages/User/Landing/HeroWidget.vue';
import HighlightsWidget from '@/Pages/User/Landing/HighlightsWidget.vue';
import PricingWidget from '@/Pages/User/Landing/PricingWidget.vue';
import TopbarWidget from '@/Pages/User/Landing/TopbarWidget.vue';

const props = defineProps(['data']);
</script>

<template>
  <div class="bg-zinc-950 text-white min-h-screen font-sans selection:bg-amber-500 selection:text-zinc-950">
    <div id="home" class="landing-wrapper overflow-hidden">
      <div class="py-4 px-6 mx-0 md:mx-12 lg:mx-20 lg:px-20 flex items-center justify-between relative lg:static z-50">
        <TopbarWidget />
      </div>
      <HeroWidget />
      <!-- <FeaturesWidget /> -->
      <HighlightsWidget />
      <PricingWidget :tablePrices="props.data.tablePrices" />
      <FooterWidget />
    </div>
  </div>
</template>
