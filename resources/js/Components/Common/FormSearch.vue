<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import { useForm } from '@inertiajs/inertia-vue3';
import $ from 'jquery';
import { Form as VeeForm } from 'vee-validate';
</script>
<template>
  <div class="flex justify-content-flex-end mb-2">
    <VeeForm as="div" v-slot="{ handleSubmit }">
      <form class="form-inline" method="POST" @submit="handleSubmit($event, onSubmit)" ref="formData">
        <div>
          <InputText type="text" class="w-300" name="free_word" id="free_word" placeholder="検索" v-model="model.free_word" />

          <Button type="submit" class="ml-2"> <i class="pi pi-search"></i> &nbsp; 検索 </Button>
          <Link :href="createUrl" class="ml-2"><Button icon="pi pi-plus" label="新規登録" /></Link>
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
      })
    };
  },
  props: ['routeName', 'createUrl', 'request'],
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
