<script setup>
import GuestLayout from '@/Layouts/User/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import { ref, reactive } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, configure, defineRule } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import axios from 'axios';

const props = defineProps(['data']);

const state = reactive({
  model: {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  }
});

const onSubmit = () => {
  useForm(state.model).post(route('user.register.store'));
};

const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if ($('[name="' + firstInputError + '"]').hasClass('hidden') || $('[name="' + firstInputError + '"]').attr('type') == 'hidden') {
    ele = $('[name="' + firstInputError + '"]').closest('div');
  }
  ele.focus();
};
defineRule('unique_custom', (value) => {
  return axios
    .post(route('user.customer.checkEmail'), {
      _token: Laravel.csrfToken,
      value: value,
      id: props.data.customers?.id
    })
    .then(function (response) {
      return response.data.valid;
    })
    .catch((error) => {});
});
let messError = {
  en: {
    fields: {
      name: { required: 'Vui lòng nhập họ tên.', max: 'Tối đa 255 ký tự.' },
      email: {
        required: 'Vui lòng nhập email.',
        email: 'Định dạng email không hợp lệ.',
        max: 'Tối đa 255 ký tự.',
        unique_custom: 'Email này đã được đăng ký.'
      },
      password: { required: 'Vui lòng nhập mật khẩu.', max: 'Tối đa 100 ký tự.', min: 'Ít nhất 8 ký tự.' },
      password_confirmation: { required: 'Vui lòng xác nhận mật khẩu.', confirmed: 'Mật khẩu xác nhận không khớp.' }
    }
  }
};
configure({ generateMessage: localize(messError) });
</script>

<template>
  <GuestLayout>
    <div class="mb-8 text-center">
      <h1 class="text-2xl font-bold font-serif text-white">Tạo tài khoản VIP</h1>
      <p class="text-zinc-400 mt-2">Trở thành hội viên để nhận các ưu đãi độc quyền</p>
    </div>

    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)">
        <div class="mb-4">
          <label class="block text-zinc-300 font-medium mb-1">Họ và tên</label>
          <Field name="name" rules="required|max:255" v-model="state.model.name" v-slot="{ field, meta, handleChange }">
            <InputText
              v-model="state.model.name"
              v-bind="field"
              :class="{ 'p-invalid': !meta.valid && meta.touched, 'w-full bg-zinc-800/50 border-zinc-700 text-white placeholder:text-zinc-500 focus:border-amber-500 focus:ring-amber-500': true }"
              @update:model-value="handleChange"
              placeholder="Nguyễn Văn A"
            />
          </Field>
          <ErrorMessage class="text-red-500 text-sm mt-1 block" name="name" />
        </div>

        <div class="mb-4">
          <label class="block text-zinc-300 font-medium mb-1">Địa chỉ Email</label>
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

        <div class="mb-4">
          <label class="block text-zinc-300 font-medium mb-1">Mật khẩu</label>
          <Field name="password" rules="required|max:100|min:8" v-model="state.model.password" v-slot="{ field, meta, handleChange }">
            <Password
              v-model="state.model.password"
              v-bind="field"
              inputClass="w-full bg-zinc-800/50 border-zinc-700 text-white placeholder:text-zinc-500 focus:border-amber-500 focus:ring-amber-500"
              :class="{ 'p-invalid': !meta.valid && meta.touched, 'w-full': true }"
              placeholder="••••••••"
              hideIcon="pi pi-eye text-zinc-400"
              showIcon="pi pi-eye-slash text-zinc-400"
              :feedback="false"
              v-on:update:model-value="handleChange"
              toggleMask
            />
          </Field>
          <ErrorMessage class="text-red-500 text-sm mt-1 block" name="password" />
        </div>

        <div class="mb-6">
          <label class="block text-zinc-300 font-medium mb-1">Xác nhận mật khẩu</label>
          <Field name="password_confirmation" rules="required|confirmed:@password" v-model="state.model.password_confirmation" v-slot="{ field, meta, handleChange }">
            <Password
              v-model="state.model.password_confirmation"
              v-bind="field"
              inputClass="w-full bg-zinc-800/50 border-zinc-700 text-white placeholder:text-zinc-500 focus:border-amber-500 focus:ring-amber-500"
              :class="{ 'p-invalid': !meta.valid && meta.touched, 'w-full': true }"
              placeholder="••••••••"
              hideIcon="pi pi-eye text-zinc-400"
              showIcon="pi pi-eye-slash text-zinc-400"
              :feedback="false"
              v-on:update:model-value="handleChange"
              toggleMask
            />
          </Field>
          <ErrorMessage class="text-red-500 text-sm mt-1 block" name="password_confirmation" />
        </div>

        <Button label="Đăng ký tài khoản" type="submit" class="w-full !bg-amber-500 !border-amber-500 !text-zinc-950 font-bold uppercase tracking-wider py-3 hover:!bg-amber-400 transition-colors shadow-[0_0_15px_rgba(245,158,11,0.2)]" />
      </form>
    </VeeForm>

    <div class="mt-8 text-center">
      <span class="text-zinc-400 text-sm">Đã có tài khoản?</span>
      <Link :href="route('user.login.index')" class="ml-2 text-sm text-amber-500 hover:text-amber-400 font-bold transition-colors">Đăng nhập</Link>
    </div>
  </GuestLayout>
</template>
