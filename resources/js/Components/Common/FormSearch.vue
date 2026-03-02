<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import { useForm } from '@inertiajs/inertia-vue3';
import $ from 'jquery';
import { Form as VeeForm } from 'vee-validate';
import axios from 'axios';
import { ref, onMounted, reactive } from 'vue';
import { useRequestStore } from '@/store/request';
const linkDownload = ref(null);
const state = reactive({
  urlDownload: ''
});
const props = defineProps(['routeName', 'createUrl', 'request', 'csvRoute']);
const exportCsv = () => {
  useRequestStore().showLoading();
  axios
    .post(route(props.csvRoute, props.request))
    .then(function (res) {
      state.urlDownload = res.data.url;
      setTimeout(function () {
        useRequestStore().hideLoading();
        linkDownload.value.click();
      }, 500);
    })
    .catch(() => {
      useRequestStore().hideLoading();
    });
};
</script>
<template>
  <div class="flex justify-content-flex-end mb-2">
    <VeeForm as="div" v-slot="{ handleSubmit }">
      <form class="form-inline" method="POST" @submit="handleSubmit($event, onSubmit)" ref="formData">
        <div>
          <InputText type="text" class="w-300" name="free_word" id="free_word" placeholder="Search" v-model="model.free_word" />
          <a :href="state.urlDownload" id="linkDownload" ref="linkDownload" download></a>
          <Button type="submit" class="ml-2"> <i class="pi pi-search"></i> &nbsp; Search </Button>
          <Link v-if="createUrl" :href="createUrl" class="ml-2"><Button icon="pi pi-plus" label="Add" /></Link>
          <Button v-if="csvRoute" @click.stop="exportCsv" class="ml-2" type="button" icon="pi pi-download" label="CSV出力" />
        </div>
      </form>
    </VeeForm>
  </div>
</template>
<script>
export default {
  mounted() {},
  created: function () {},
  data() {
    return {
      model: useForm({
        sort: this.request.sort,
        direction: this.request.direction,
        free_word: this.request.free_word
      }),
      urlDownload: ''
    };
  },
  props: ['routeName', 'createUrl', 'request', 'csvRoute'],
  methods: {
    onSubmit() {
      this.model.get(route(this.routeName));
    }
  }
};
</script>
<style scoped>
.justify-content-flex-end {
  justify-content: flex-end;
}
</style>
>
