<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, onMounted, reactive } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import axios from 'axios';

const page = usePage();
const fileInput = ref(null);
const previewUrl = ref(null);
const state = reactive({
  model: {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    avatar: null
  }
});

const triggerFileInput = () => {
  fileInput.value.click();
};

const onFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    state.model.avatar = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};
const props = defineProps(['data']);
onMounted(() => {
  if (props.data.isEdit) {
    state.model = { ...props.data.customer, password: '', password_confirmation: '' };
  }
});
const flagValidateUnique = ref(true);
defineRule('unique_custom', (value) => {
  return axios
    .post(route('admin.customer.checkEmail'), {
      _token: Laravel.csrfToken,
      value: value,
      id: props.data.customer?.id
    })
    .then(function (response) {
      return response.data.valid;
    })
    .catch((error) => {});
});
const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if ($('[name="' + firstInputError + '"]').hasClass('hidden') || $('[name="' + firstInputError + '"]').attr('type') == 'hidden') {
    ele = $('[name="' + firstInputError + '"]').closest('div');
  }
  ele.focus();
  $('html, body').animate(
    {
      scrollTop: ele.offset().top - 150 + 'px'
    },
    500
  );
};
let messError = {
  en: {
    fields: {
      name: {
        required: 'Vui lòng nhập tên khách hàng.',
        max: 'Tên khách hàng không được vượt quá 255 ký tự.'
      },
      email: {
        required: 'Vui lòng nhập email.',
        max: 'Email không được vượt quá 255 ký tự.',
        unique_custom: 'Email này đã được đăng ký.',
        email: 'Vui lòng nhập đúng định dạng email.'
      },
      phone: {
        max: 'Số điện thoại không được vượt quá 20 ký tự.'
      },
      password: {
        required: 'Vui lòng nhập mật khẩu.',
        max: 'Mật khẩu phải từ 10 đến 16 ký tự gồm chữ thường, chữ hoa và số.',
        min: 'Mật khẩu phải từ 10 đến 16 ký tự gồm chữ thường, chữ hoa và số.',
        password_rule: 'Mật khẩu phải từ 10 đến 16 ký tự gồm chữ thường, chữ hoa và số.'
      },
      password_confirmation: {
        required: 'Vui lòng xác nhận mật khẩu.',
        confirmed: 'Mật khẩu xác nhận không khớp.'
      }
    }
  }
};
configure({
  generateMessage: localize(messError)
});
const onSubmit = () => {
  if (props.data.isEdit) {
    useForm({
      ...state.model,
      _method: 'put'
    }).post(route('admin.customer.update', props.data.customer.id));
    return;
  } else {
    useForm(state.model).post(route('admin.customer.store'));
  }
};
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <template #header>
          <div class="flex items-center">
            <span class="font-bold">
              {{ $page.props.data.title }}
            </span>
          </div>
        </template>
        <template #icons>
          <Link :href="$page.props.data.urlBack">
            <Button label="Hủy " icon="pi pi-arrow-left" class="btn-action"></Button>
          </Link>
          <Button label="Lưu" type="submit" form="customer-form" icon="pi pi-save" class="btn-action ml-2" />
        </template>
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" id="customer-form" class="form-data">
            <div class="flex flex-wrap gap-6">
              <div class="w-full md:w-1/4 lg:w-1/5 flex flex-col items-center gap-4">
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onFileChange" />
                <div class="relative group cursor-pointer" @click="triggerFileInput">
                  <img
                    :src="previewUrl || state.model.avatar_url || '/images/default-avatar.svg'"
                    alt="Avatar"
                    class="w-32 h-32 md:w-60 md:h-60 rounded-full object-cover border-4 border-white shadow-lg group-hover:opacity-75 transition duration-300"
                  />
                  <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 bg-black bg-opacity-30 rounded-full">
                    <span class="text-white text-sm font-bold"><i class="pi pi-camera mr-2"></i>Thay đổi</span>
                  </div>
                </div>
                <div class="text-center">
                  <p class="text-sm text-gray-500 mb-2">Ảnh đại diện</p>
                  <Button label="Thay đổi ảnh" icon="pi pi-image" class="p-button-outlined p-button-secondary" @click="triggerFileInput" type="button" />
                </div>
              </div>
              <div class="flex-1 border rounded-lg p-4 bg-gray-50 shadow-sm">
                <div class="form-group">
                  <label class="form-label" require>Tên khách hàng: </label>
                  <div class="form-input">
                    <Field name="name" rules="required|max:255" v-model="state.model.name" v-slot="{ field, meta: metaField, handleChange }">
                      <InputText
                        class="w-full"
                        type="text"
                        v-model="state.model.name"
                        v-on:update:model-value="handleChange"
                        v-bind="field"
                        :class="{
                          'p-invalid': !metaField.valid && metaField.touched
                        }"
                      />
                      <ErrorMessage class="p-error" name="name" />
                    </Field>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label" require>Email:</label>
                  <div class="form-input">
                    <Field name="email" :rules="flagValidateUnique ? 'required|email|max:255|unique_custom' : 'required|email|max:255'" v-model="state.model.email" v-slot="{ field, meta: metaField, handleChange }">
                      <InputText
                        class="w-full"
                        @keypress="flagValidateUnique = false"
                        @blur="flagValidateUnique = true"
                        v-model="state.model.email"
                        v-bind="field"
                        autocomplete="username"
                        v-on:update:model-value="handleChange"
                        :class="{
                          'p-invalid': !metaField.valid && metaField.touched
                        }"
                      />
                      <ErrorMessage class="p-error" name="email" />
                    </Field>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Số điện thoại:</label>
                  <div class="form-input">
                    <Field name="phone" rules="max:20" v-model="state.model.phone" v-slot="{ field, meta: metaField, handleChange }">
                      <InputText
                        class="w-full"
                        v-model="state.model.phone"
                        v-bind="field"
                        v-on:update:model-value="handleChange"
                        :class="{
                          'p-invalid': !metaField.valid && metaField.touched
                        }"
                      />
                      <ErrorMessage class="p-error" name="phone" />
                    </Field>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label" v-if="props.data.isEdit"> Đổi mật khẩu: </label>
                  <label class="form-label" v-else require>Mật khẩu: </label>
                  <div class="form-input">
                    <Field name="password" :rules="props.data.isEdit ? 'max:16|min:10|password_rule' : 'required|max:16|min:10|password_rule'" v-model="state.model.password" v-slot="{ field, meta: metaField, handleChange }">
                      <Password
                        v-bind="field"
                        v-model="state.model.password"
                        inputClass="w-full"
                        placeholder="Nhập mật khẩu"
                        hideIcon="pi pi-eye"
                        showIcon="pi pi-eye-slash"
                        :feedback="false"
                        aria-describedby="password-error"
                        :inputProps="{ autocomplete: 'new-password' }"
                        v-on:update:model-value="handleChange"
                        toggleMask
                        class="w-full"
                        :id="'password'"
                        :class="{
                          'p-invalid': !metaField.valid && metaField.touched
                        }"
                      />
                      <ErrorMessage class="p-error" name="password" />
                    </Field>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label" v-if="props.data.isEdit"> Xác nhận mật khẩu mới: </label>
                  <label class="form-label" v-else require>Xác nhận mật khẩu: </label>
                  <div class="form-input">
                    <Field
                      name="password_confirmation"
                      :rules="props.data.isEdit ? (state.model.password ? 'required|confirmed:@password' : '') : 'required|confirmed:@password'"
                      v-model="state.model.password_confirmation"
                      v-slot="{ field, meta: metaField, handleChange }"
                    >
                      <Password
                        v-bind="field"
                        v-model="state.model.password_confirmation"
                        inputClass="w-full"
                        placeholder="Xác nhận mật khẩu"
                        hideIcon="pi pi-eye"
                        showIcon="pi pi-eye-slash"
                        :feedback="false"
                        aria-describedby="password-confirmation-error"
                        :inputProps="{ autocomplete: 'new-password' }"
                        v-on:update:model-value="handleChange"
                        toggleMask
                        class="w-full"
                        :class="{
                          'p-invalid': !metaField.valid && metaField.touched
                        }"
                      />
                      <ErrorMessage class="p-error" name="password_confirmation" />
                    </Field>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>
