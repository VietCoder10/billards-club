<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { ref, onMounted, reactive } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import axios from 'axios';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Panel from 'primevue/panel';

const page = usePage();
const props = defineProps(['data']);

const fileInput = ref(null);
const previewUrl = ref(null);

const state = reactive({
  model: {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    avatar: null,
    avatar_url: ''
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

onMounted(() => {
  if (props.data.user) {
    state.model.name = props.data.user.name || '';
    state.model.email = props.data.user.email || '';
    state.model.phone = props.data.user.phone || '';
    state.model.avatar_url = props.data.user.avatar_url || '';
  }
});

const flagValidateUnique = ref(true);

defineRule('unique_custom', (value) => {
  if (!value) return true;
  return axios
    .post(route('user.profile.checkEmail'), {
      _token: Laravel.csrfToken,
      value: value,
      id: props.data.user?.id
    })
    .then(function (response) {
      return response.data.valid;
    })
    .catch(() => true);
});

const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if (ele.hasClass('hidden') || ele.attr('type') == 'hidden') {
    ele = ele.closest('div');
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
  vi: {
    fields: {
      name: {
        required: 'Vui lòng nhập họ tên.',
        max: 'Họ tên không được vượt quá 255 ký tự.'
      },
      email: {
        required: 'Vui lòng nhập email.',
        max: 'Email không được vượt quá 255 ký tự.',
        unique_custom: 'Email này đã được sử dụng.',
        email: 'Vui lòng nhập đúng định dạng email.'
      },
      phone: {
        max: 'Số điện thoại không được vượt quá 20 ký tự.',
        telephone: 'Số điện thoại không đúng định dạng.'
      },
      password: {
        max: 'Mật khẩu phải từ 10 đến 16 ký tự.',
        min: 'Mật khẩu phải từ 10 đến 16 ký tự.',
        password_rule: 'Mật khẩu phải từ 10 đến 16 ký tự gồm chữ hoa, chữ thường và số.'
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
  const formFields = { ...state.model };
  if (!(formFields.avatar instanceof File)) {
    delete formFields.avatar;
  }
  useForm(formFields).post(route('user.profile.update'));
};
</script>

<template>
  <UserLayout>
    <template #content>
      <div class="py-4">
        <!-- Main Card Wrapper -->
        <div class="bg-white dark:bg-zinc-900 rounded-3xl overflow-hidden shadow-2xl border border-zinc-100 dark:border-zinc-800 transition duration-300">
          <!-- Decorative Top Gradient Header -->
          <div class="h-36 bg-gradient-to-r from-emerald-500 via-teal-600 to-indigo-600 flex items-center px-8 relative">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex items-center justify-between w-full">
              <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-wide">Thông tin cá nhân</h1>
                <p class="text-teal-100 text-xs md:text-sm mt-1">Cập nhật tài khoản thành viên VIP Billiards Club</p>
              </div>
              <div class="flex gap-2">
                <Link :href="route('user.dashboard.index')">
                  <Button label="Quay lại" icon="pi pi-arrow-left" class="!bg-white/20 !border-white/10 !text-white hover:!bg-white/30 transition duration-200" />
                </Link>
                <Button label="Lưu" type="submit" form="profile-form" icon="pi pi-save" class="!bg-emerald-500 !border-emerald-500 !text-white hover:!bg-emerald-400 transition duration-200 shadow-md shadow-emerald-500/20" />
              </div>
            </div>
          </div>

          <div class="p-8 md:p-12">
            <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
              <form @submit="handleSubmit($event, onSubmit)" id="profile-form" class="space-y-8">
                <div class="flex flex-col lg:flex-row gap-12">
                  <!-- Left column: Avatar and Card summary -->
                  <div class="w-full lg:w-1/3 flex flex-col items-center">
                    <div class="bg-gradient-to-b from-zinc-50 to-zinc-100/50 dark:from-zinc-800/50 dark:to-zinc-900/50 rounded-3xl p-8 border border-zinc-100 dark:border-zinc-800/80 shadow-inner w-full flex flex-col items-center">
                      <!-- Avatar Area -->
                      <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onFileChange" />
                      <div class="relative group cursor-pointer mb-6" @click="triggerFileInput">
                        <div class="w-36 h-36 md:w-48 md:h-48 rounded-full overflow-hidden border-4 border-white dark:border-zinc-800 shadow-xl relative transition duration-300 group-hover:scale-105">
                          <img :src="previewUrl || state.model.avatar_url || '/images/default-avatar.svg'" alt="Avatar" class="w-full h-full object-cover" />
                        </div>
                        <!-- Upload Hover Overlay -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 bg-black/40 rounded-full border-4 border-transparent">
                          <i class="pi pi-camera text-white text-3xl mb-1"></i>
                          <span class="text-white text-xs font-semibold">Tải ảnh lên</span>
                        </div>
                      </div>

                      <div class="text-center w-full">
                        <h3 class="text-lg font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ state.model.name || 'Thành viên mới' }}</h3>
                        <p class="text-emerald-500 font-bold text-xs uppercase tracking-wider mt-1 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1 rounded-full inline-block">Hội viên VIP</p>

                        <div class="mt-6 pt-6 border-t border-zinc-200/60 dark:border-zinc-800 w-full space-y-3 text-left text-sm text-zinc-500 dark:text-zinc-400">
                          <div class="flex items-center gap-3">
                            <i class="pi pi-envelope text-zinc-400"></i>
                            <span class="truncate">{{ state.model.email || 'Chưa cập nhật' }}</span>
                          </div>
                          <div class="flex items-center gap-3">
                            <i class="pi pi-phone text-zinc-400"></i>
                            <span>{{ state.model.phone || 'Chưa cập nhật' }}</span>
                          </div>
                          <div class="flex items-center gap-3">
                            <i class="pi pi-calendar text-zinc-400"></i>
                            <span>Tham gia: {{ $page.props.user?.created_at ? $page.props.user?.created_at : 'Vừa xong' }}</span>
                          </div>
                        </div>

                        <button
                          type="button"
                          @click="triggerFileInput"
                          class="mt-6 w-full py-2.5 px-4 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold rounded-xl transition duration-200 flex items-center justify-center gap-2"
                        >
                          <i class="pi pi-image"></i>
                          Đổi ảnh đại diện
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Right column: Main profile info edit -->
                  <div class="flex-1 space-y-6">
                    <!-- Basic Info Panel -->
                    <div class="bg-zinc-50/50 dark:bg-zinc-800/20 rounded-2xl p-6 md:p-8 border border-zinc-100 dark:border-zinc-800/60 space-y-6">
                      <h4 class="text-md font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-2 pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
                        <i class="pi pi-id-card text-emerald-500"></i>
                        Thông tin cơ bản
                      </h4>

                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div class="flex flex-col gap-1.5">
                          <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">Họ và tên <span class="text-red-500">*</span></label>
                          <Field name="name" rules="required|max:255" v-model="state.model.name" v-slot="{ field, meta, handleChange }">
                            <InputText class="w-full !rounded-xl" type="text" v-model="state.model.name" v-on:update:model-value="handleChange" v-bind="field" :class="{ 'p-invalid': !meta.valid && meta.touched }" placeholder="Nhập họ và tên" />
                            <ErrorMessage class="text-red-500 text-xs mt-1 font-medium block" name="name" />
                          </Field>
                        </div>

                        <!-- Phone Number -->
                        <div class="flex flex-col gap-1.5">
                          <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">Số điện thoại</label>
                          <Field name="phone" rules="max:20|telephone" v-model="state.model.phone" v-slot="{ field, meta, handleChange }">
                            <InputText class="w-full !rounded-xl" type="text" v-model="state.model.phone" v-on:update:model-value="handleChange" v-bind="field" :class="{ 'p-invalid': !meta.valid && meta.touched }" placeholder="Nhập số điện thoại" />
                            <ErrorMessage class="text-red-500 text-xs mt-1 font-medium block" name="phone" />
                          </Field>
                        </div>

                        <!-- Email Address -->
                        <div class="flex flex-col gap-1.5 md:col-span-2">
                          <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">Địa chỉ Email <span class="text-red-500">*</span></label>
                          <Field name="email" :rules="flagValidateUnique ? 'required|email|max:255|unique_custom' : 'required|email|max:255'" v-model="state.model.email" v-slot="{ field, meta, handleChange }">
                            <InputText
                              class="w-full !rounded-xl"
                              @keypress="flagValidateUnique = false"
                              @blur="flagValidateUnique = true"
                              v-model="state.model.email"
                              v-bind="field"
                              autocomplete="username"
                              v-on:update:model-value="handleChange"
                              :class="{ 'p-invalid': !meta.valid && meta.touched }"
                              placeholder="example@email.com"
                            />
                            <ErrorMessage class="text-red-500 text-xs mt-1 font-medium block" name="email" />
                          </Field>
                        </div>
                      </div>
                    </div>

                    <!-- Security / Password Panel -->
                    <div class="bg-zinc-50/50 dark:bg-zinc-800/20 rounded-2xl p-6 md:p-8 border border-zinc-100 dark:border-zinc-800/60 space-y-6">
                      <h4 class="text-md font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-2 pb-3 border-b border-zinc-200/60 dark:border-zinc-800">
                        <i class="pi pi-lock text-indigo-500"></i>
                        Đổi mật khẩu mới
                      </h4>

                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div class="flex flex-col gap-1.5">
                          <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">Mật khẩu mới</label>
                          <Field name="password" rules="max:16|min:10|password_rule" v-model="state.model.password" v-slot="{ field, meta, handleChange }">
                            <Password
                              v-bind="field"
                              v-model="state.model.password"
                              inputClass="w-full !rounded-xl"
                              placeholder="Nhập mật khẩu mới"
                              hideIcon="pi pi-eye text-zinc-400"
                              showIcon="pi pi-eye-slash text-zinc-400"
                              :feedback="false"
                              :inputProps="{ autocomplete: 'new-password' }"
                              v-on:update:model-value="handleChange"
                              toggleMask
                              class="w-full"
                              :id="'password'"
                              :class="{ 'p-invalid': !meta.valid && meta.touched }"
                            />
                            <ErrorMessage class="text-red-500 text-xs mt-1 font-medium block" name="password" />
                          </Field>
                        </div>

                        <!-- Confirm Password -->
                        <div class="flex flex-col gap-1.5">
                          <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">Xác nhận mật khẩu mới</label>
                          <Field name="password_confirmation" :rules="state.model.password ? 'required|confirmed:@password' : ''" v-model="state.model.password_confirmation" v-slot="{ field, meta, handleChange }">
                            <Password
                              v-bind="field"
                              v-model="state.model.password_confirmation"
                              inputClass="w-full !rounded-xl"
                              placeholder="Xác nhận mật khẩu mới"
                              hideIcon="pi pi-eye text-zinc-400"
                              showIcon="pi pi-eye-slash text-zinc-400"
                              :feedback="false"
                              :inputProps="{ autocomplete: 'new-password' }"
                              v-on:update:model-value="handleChange"
                              toggleMask
                              class="w-full"
                              :class="{ 'p-invalid': !meta.valid && meta.touched }"
                            />
                            <ErrorMessage class="text-red-500 text-xs mt-1 font-medium block" name="password_confirmation" />
                          </Field>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </VeeForm>
          </div>
        </div>
      </div>
    </template>
  </UserLayout>
</template>

<style scoped>
:deep(.p-panel) {
  border: none;
}
:deep(.p-inputtext) {
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
}
:deep(.p-password input) {
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
}
</style>
