<script setup>
import GuestLayout from '@/Layouts/User/GuestLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { reactive, onMounted } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import Password from 'primevue/password';
import Button from 'primevue/button';

const props = defineProps(['data']);
const state = reactive({
  model: {
    password: '',
    password_confirmation: ''
  },
  token: ''
});

onMounted(() => {
  state.token = props.data?.token || '';
});

const onSubmit = () => {
  useForm(state.model).put(route('user.reset-password.update', props.data.token));
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
      <h1 class="text-2xl font-bold font-serif text-white">Tạo mật khẩu mới</h1>
      <p class="text-zinc-400 mt-2">Vui lòng nhập mật khẩu mới cho tài khoản của bạn</p>
    </div>

    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)">
        <div class="mb-4">
          <label class="block text-zinc-300 font-medium mb-1">Mật khẩu mới</label>
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

        <div class="mb-8">
          <label class="block text-zinc-300 font-medium mb-1">Xác nhận mật khẩu mới</label>
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

        <Button label="Lưu mật khẩu mới" type="submit" class="w-full !bg-amber-500 !border-amber-500 !text-zinc-950 font-bold uppercase tracking-wider py-3 hover:!bg-amber-400 transition-colors shadow-[0_0_15px_rgba(245,158,11,0.2)]" />
      </form>
    </VeeForm>
  </GuestLayout>
</template>
