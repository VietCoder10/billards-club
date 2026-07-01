<script setup>
import GuestLayout from '@/Layouts/User/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import { reactive } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

const state = reactive({
  model: {
    email: ''
  }
});

const onSubmit = () => {
  useForm(state.model).post(route('user.forgot-password.store'));
};

const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if (ele.hasClass('hidden') || ele.attr('type') == 'hidden') {
    ele = ele.closest('div');
  }
  ele.focus();
};

let messError = {
  en: {
    fields: {
      email: { required: 'Vui lòng nhập email.', email: 'Định dạng email không hợp lệ.', max: 'Tối đa 255 ký tự.' }
    }
  }
};
configure({ generateMessage: localize(messError) });
</script>

<template>
  <GuestLayout>
    <div class="mb-8 text-center">
      <h1 class="text-2xl font-bold font-serif text-white">Quên mật khẩu</h1>
      <p class="text-zinc-400 mt-2">Nhập email của bạn, chúng tôi sẽ gửi liên kết khôi phục</p>
    </div>

    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)">
        <div class="mb-8">
          <label class="block text-zinc-300 font-medium mb-2">Địa chỉ Email</label>
          <Field name="email" rules="required|max:255|email" v-model="state.model.email" v-slot="{ field, meta, handleChange }">
            <InputText
              v-model="state.model.email"
              v-bind="field"
              :class="{ 'p-invalid': !meta.valid && meta.touched, 'w-full bg-zinc-800/50 border-zinc-700 text-white placeholder:text-zinc-500 focus:border-amber-500 focus:ring-amber-500': true }"
              @update:model-value="handleChange"
              placeholder="example@email.com"
            />
          </Field>
          <ErrorMessage class="text-red-500 text-sm mt-1 block" name="email" />
        </div>

        <Button label="Gửi liên kết khôi phục" type="submit" class="w-full !bg-amber-500 !border-amber-500 !text-zinc-950 font-bold uppercase tracking-wider py-3 hover:!bg-amber-400 transition-colors shadow-[0_0_15px_rgba(245,158,11,0.2)]" />
      </form>
    </VeeForm>

    <div class="mt-8 text-center">
      <Link :href="route('user.login.index')" class="text-sm text-amber-500 hover:text-amber-400 font-bold transition-colors"><i class="pi pi-arrow-left mr-2"></i>Quay lại đăng nhập</Link>
    </div>
  </GuestLayout>
</template>
