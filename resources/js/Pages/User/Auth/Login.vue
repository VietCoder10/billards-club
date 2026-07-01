<script setup>
import GuestLayout from '@/Layouts/User/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import { ref, reactive, onMounted, watch } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

const state = reactive({
  model: {
    email: '',
    password: '',
    remember_me: 0,
    url_redirect: ''
  },
  message: ''
});

const props = defineProps(['data']);

const onSubmit = () => {
  useForm(state.model).post(route('user.login.store'));
};

const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if ($('[name="' + firstInputError + '"]').hasClass('hidden') || $('[name="' + firstInputError + '"]').attr('type') == 'hidden') {
    ele = $('[name="' + firstInputError + '"]').closest('div');
  }
  ele.focus();
};

onMounted(() => {
  state.model.url_redirect = props.data?.request?.url_redirect || '';
});

let messError = {
  en: {
    fields: {
      email: {
        required: 'Vui lòng nhập email.',
        email: 'Vui lòng nhập đúng định dạng email.',
        max: 'Tối đa 255 ký tự.'
      },
      password: {
        required: 'Vui lòng nhập mật khẩu.',
        max: 'Tối đa 100 ký tự.',
        min: 'Ít nhất 8 ký tự.'
      }
    }
  }
};
configure({
  generateMessage: localize(messError)
});
</script>

<template>
  <GuestLayout>
    <div class="mb-8 text-center">
      <h1 class="text-2xl font-bold text-white">Đăng nhập hệ thống</h1>
    </div>
    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)">
        <div class="mb-5">
          <label class="block text-zinc-300 font-medium mb-2">Địa chỉ Email</label>
          <Field name="email" rules="required|max:255|email" v-model="state.model.email" v-slot="{ field, meta: metaField, handleChange }">
            <InputText
              v-model="state.model.email"
              v-bind="field"
              :class="{
                'p-invalid': !metaField.valid && metaField.touched,
                'w-full bg-zinc-800/50 border-zinc-700 text-white placeholder:text-zinc-500 focus:border-amber-500 focus:ring-amber-500': true
              }"
              @update:model-value="handleChange"
              placeholder="example@email.com"
            />
          </Field>
          <ErrorMessage class="text-red-500 text-sm mt-1 block" name="email" />
        </div>

        <div class="mb-6">
          <label class="block text-zinc-300 font-medium mb-2">Mật khẩu</label>
          <Field name="password" rules="required|max:100|min:8" v-model="state.model.password" v-slot="{ field, meta: metaField, handleChange }">
            <Password
              v-bind="field"
              v-model="state.model.password"
              inputClass="w-full bg-zinc-800/50 border-zinc-700 text-white placeholder:text-zinc-500 focus:border-amber-500 focus:ring-amber-500"
              :class="{
                'p-invalid': !metaField.valid && metaField.touched,
                'w-full': true
              }"
              placeholder="••••••••"
              hideIcon="pi pi-eye text-zinc-400"
              showIcon="pi pi-eye-slash text-zinc-400"
              :feedback="false"
              :inputProps="{ autocomplete: 'off' }"
              v-on:update:model-value="handleChange"
              toggleMask
            />
            <ErrorMessage class="text-red-500 text-sm mt-1 block" name="password" />
          </Field>
        </div>

        <div class="flex items-center justify-between mb-8">
          <Link :href="route('user.forgot-password.index')" class="text-sm text-amber-500 hover:text-amber-400 transition-colors">Quên mật khẩu?</Link>
        </div>

        <Button label="Đăng nhập" type="submit" class="w-full !bg-amber-500 !border-amber-500 !text-zinc-950 font-bold uppercase tracking-wider py-3 hover:!bg-amber-400 transition-colors shadow-[0_0_15px_rgba(245,158,11,0.2)]" />
      </form>
    </VeeForm>

    <div class="mt-8 text-center">
      <span class="text-zinc-400 text-sm">Chưa có tài khoản?</span>
      <Link :href="route('user.register.index')" class="ml-2 text-sm text-amber-500 hover:text-amber-400 font-bold transition-colors">Đăng ký ngay</Link>
    </div>
  </GuestLayout>
</template>
