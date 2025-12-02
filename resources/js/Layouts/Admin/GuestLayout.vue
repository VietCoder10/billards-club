<script setup>
import SpinBeforeLoaded from '@/Components/Common/SpinBeforeLoaded.vue';
import Notyf from '@/Components/Common/Notyf.vue';
import { Head } from '@inertiajs/vue3';
import FloatingConfigurator from '@/Components/Common/FloatingConfigurator.vue';
import NotyfValidate from '@/Components/Common/NotyfValidate.vue';
</script>

<template>
  <Head :title="$page.props.data.title" />
  <div>
    <FloatingConfigurator />
    <Notyf :data="$page.props.sessionAlert" />
    <NotyfValidate :data="$page.props.errors" />
    <SpinBeforeLoaded />
    <slot name="content" />
  </div>
</template>
