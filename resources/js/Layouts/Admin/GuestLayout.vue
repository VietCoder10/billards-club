<script setup>
import SpinBeforeLoaded from '@/Components/Common/SpinBeforeLoaded.vue';
import Notyf from '@/Components/Common/Notyf.vue';
import FloatingConfigurator from '@/Components/Common/FloatingConfigurator.vue';
</script>

<template>
  <div>
    <FloatingConfigurator />
    <Notyf :data="$page.props.sessionAlert" />
    <SpinBeforeLoaded />
    <slot name="content" />
  </div>
</template>
