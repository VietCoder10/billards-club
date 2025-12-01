<script setup>
import SpinBeforeLoaded from '@/Components/Common/SpinBeforeLoaded.vue';
import Notyf from '@/Components/Common/Notyf.vue';
import { Head } from '@inertiajs/vue3';
import FloatingConfigurator from '@/Components/Common/FloatingConfigurator.vue';
</script>

<template>
  <Head :title="$page.props.data.title" />
  <div>
    <FloatingConfigurator />
    <Notyf :data="$page.props.sessionAlert" />
    <SpinBeforeLoaded />
    <slot name="content" />
  </div>
</template>
