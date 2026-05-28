<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { Field, ErrorMessage, Form as VeeForm, configure, defineRule } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import { useToastStore } from '@/store/toast';
import { useRequestStore } from '@/store/request';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import FloatLabel from 'primevue/floatlabel';

const toastStore = useToastStore();
const requestStore = useRequestStore();

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:visible', 'saved']);

const localVisible = ref(props.visible);

const flagValidateUnique = ref(true);
defineRule('unique_custom', (value) => {
  return axios
    .post(route('admin.customer.checkEmail'), {
      _token: Laravel.csrfToken,
      value: value,
      id: null
    })
    .then(function (response) {
      return response.data.valid;
    })
    .catch((error) => {});
});

watch(
  () => props.visible,
  (v) => {
    state.model = { ...initialState };
    localVisible.value = v;
  }
);

watch(localVisible, (v) => emit('update:visible', v));

const initialState = {
  name: '',
  email: '',
  tel: '',
  password: ''
};

const state = reactive({
  model: { ...initialState }
});

const handleCancel = () => {
  localVisible.value = false;
};

onMounted(() => {
  setMessageError();
});

const setMessageError = () => {
  let messError = {
    ja: {
      fields: {
        name: {
          required: 'Tên khách hàng không được để trống。',
          max: 'Tên khách hàng không được vượt quá 255 ký tự。'
        },
        email: {
          required: 'Email không được để trống。',
          email: 'Email không đúng định dạng。',
          max: 'Email không được vượt quá 255 ký tự。',
          unique_custom: 'Email này đã được đăng ký。'
        },
        tel: {
          required: 'Số điện thoại không được để trống。',
          max: 'Số điện thoại không được vượt quá 255 ký tự。'
        },
        password: {
          required: 'Mật khẩu không được để trống。',
          min: 'Mật khẩu phải có ít nhất 8 ký tự。'
        }
      }
    }
  };

  configure({
    generateMessage: localize(messError)
  });
};
setLocale('ja');

const onSubmit = async () => {
  requestStore.showLoading();
  try {
    const response = await axios.post(
      route('admin.customer.storeModalCustomer'),
      { ...state.model },
      {
        headers: {
          Accept: 'application/json'
        }
      }
    );
    if (response?.data?.message) {
      toastStore.setToast(response.data.message, 'success');
    }
    localVisible.value = false;
    emit('saved', response.data.customer);
  } catch (error) {
    toastStore.setToast('Đã có lỗi xảy ra。', 'error');
  } finally {
    requestStore.hideLoading();
  }
};
</script>

<template>
  <Dialog v-model:visible="localVisible" modal header="Thêm khách hàng" :dismissableMask="true" :style="{ width: '40vw' }" :breakpoints="{ '960px': '60vw', '640px': '90vw' }">
    <VeeForm as="div" v-slot="{ handleSubmit }">
      <form @submit="handleSubmit($event, onSubmit)" class="space-y-6 py-4">
        <div class="field">
          <Field name="name" rules="required|max:255" v-model="state.model.name" v-slot="{ field, meta, handleChange }">
            <FloatLabel variant="on">
              <InputText id="add_name" class="w-full" :modelValue="field.value" @update:modelValue="handleChange" :class="{ 'p-invalid': !meta.valid && meta.touched }" />
              <label for="add_name">Họ và tên <span class="text-red-500">(bắt buộc)</span></label>
            </FloatLabel>
            <ErrorMessage class="p-error text-xs" name="name" />
          </Field>
        </div>

        <div class="field">
          <Field name="email" :rules="flagValidateUnique ? 'required|email|max:255|unique_custom' : 'required|email|max:255'" v-model="state.model.email" v-slot="{ field, meta, handleChange }">
            <FloatLabel variant="on">
              <InputText id="add_email" class="w-full" @keypress="flagValidateUnique = false" @blur="flagValidateUnique = true" :modelValue="field.value" @update:modelValue="handleChange" v-bind="field" :class="{ 'p-invalid': !meta.valid && meta.touched }" />
              <label for="add_email">Email <span class="text-red-500">(bắt buộc)</span></label>
            </FloatLabel>
            <ErrorMessage class="p-error text-xs" name="email" />
          </Field>
        </div>

        <div class="field">
          <Field name="tel" rules="required|max:255" v-model="state.model.tel" v-slot="{ field, meta, handleChange }">
            <FloatLabel variant="on">
              <InputText id="add_tel" class="w-full" :modelValue="field.value" @update:modelValue="handleChange" :class="{ 'p-invalid': !meta.valid && meta.touched }" />
              <label for="add_tel">Số điện thoại <span class="text-red-500">(bắt buộc)</span></label>
            </FloatLabel>
            <ErrorMessage class="p-error text-xs" name="tel" />
          </Field>
        </div>

        <div class="field">
          <Field name="password" rules="required|min:8" v-model="state.model.password" v-slot="{ field, meta, handleChange }">
            <FloatLabel variant="on">
              <InputText id="add_password" type="password" class="w-full" :modelValue="field.value" @update:modelValue="handleChange" :class="{ 'p-invalid': !meta.valid && meta.touched }" />
              <label for="add_password">Mật khẩu <span class="text-red-500">(bắt buộc)</span></label>
            </FloatLabel>
            <ErrorMessage class="p-error text-xs" name="password" />
          </Field>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Button label="Hủy" icon="pi pi-times" severity="secondary" @click="handleCancel" />
          <Button label="Lưu" icon="pi pi-save" type="submit" />
        </div>
      </form>
    </VeeForm>
  </Dialog>
</template>
