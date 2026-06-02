<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { ref, reactive, onMounted } from 'vue';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import axios from 'axios';

const state = reactive({
  model: {
    supplier_name: '',
    email: '',
    phone: '',
    address: '',
    contact_person: '',
    status: '1', // mặc định Active
    note: ''
  }
});
const props = defineProps(['data']);

onMounted(() => {
  if (props.data.isEdit) {
    state.model = props.data.supplier;
  }
  setMessageError();
});

// Flag to handle unique email validation dynamically
const flagValidateUnique = ref(true);

// Validation rule for unique email
// defineRule('unique_email', (value) => {
//   if (!value) return true;
//   return axios
//     .post(route('admin.supplier.checkEmail'), {
//       _token: Laravel.csrfToken,
//       email: value,
//       id: props.data.supplier?.id
//     })
//     .then((res) => res.data.valid)
//     .catch(() => true);
// });

const setMessageError = () => {
  let messError = {
    en: {
      fields: {
        supplier_name: {
          required: 'Tên nhà cung cấp là bắt buộc.',
          max: 'Tên nhà cung cấp không được vượt quá 255 ký tự.'
        },
        email: {
          required: 'Email là bắt buộc.',
          email: 'Email không hợp lệ.',
          unique_email: 'Email đã tồn tại.'
        },
        phone: {
          required: 'Số điện thoại là bắt buộc.',
          numeric: 'Số điện thoại chỉ được chứa số.',
          max: 'Số điện thoại không được vượt quá 10 ký tự.'
        },
        address: {
          required: 'Địa chỉ là bắt buộc.',
          max: 'Địa chỉ không được vượt quá 255 ký tự.'
        },
        contact_person: {
          max: 'Người liên hệ không được vượt quá 255 ký tự.'
        },
        status: {
          required: 'Trạng thái là bắt buộc.'
        },
        note: {
          max: 'Ghi chú không được vượt quá 255 ký tự.'
        }
      }
    }
  };
  configure({
    generateMessage: localize(messError)
  });
};
const onInvalidSubmit = ({ errors }) => {
  const firstInput = Object.keys(errors)[0];
  const ele = document.querySelector(`[name="${firstInput}"]`);
  if (ele) ele.focus();
};

// Submit form
const onSubmit = () => {
  const form = useForm(state.model);
  if (props.data.isEdit) {
    form.put(route('admin.supplier.update', props.data.supplier.id));
  } else {
    form.post(route('admin.supplier.store'));
  }
};
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" class="form-data">
            <div class="card flex flex-col gap-4">
              <div class="flex flex-wrap gap-4">
                <!-- Supplier Name -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field v-model="state.model.supplier_name" name="supplier_name" rules="required|max:255" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputText :modelValue="field.value" @update:modelValue="handleChange" class="w-full" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label for="supplier_name">Tên nhà cung cấp <span class="required">(Bắt buộc)</span></label>
                    </FloatLabel>
                    <ErrorMessage name="supplier_name" class="p-error" />
                  </Field>
                </div>

                <!-- Email -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field v-model="state.model.email" name="email" :rules="flagValidateUnique ? 'required|email|max:255' : 'required|email|max:255'" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputText :modelValue="field.value" @update:modelValue="handleChange" class="w-full" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" @keypress="flagValidateUnique = false" @blur="flagValidateUnique = true" />
                      <label for="email">Email</label>
                    </FloatLabel>
                    <ErrorMessage name="email" class="p-error" />
                  </Field>
                </div>
              </div>

              <div class="flex flex-wrap gap-4 mt-4">
                <!-- Phone -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field v-model="state.model.phone" name="phone" rules="required|max:10|numeric" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputText :modelValue="field.value" @update:modelValue="handleChange" class="w-full" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label for="phone">Số điện thoại <span class="required">(Bắt buộc)</span></label>
                    </FloatLabel>
                    <ErrorMessage name="phone" class="p-error" />
                  </Field>
                </div>

                <!-- Address -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field v-model="state.model.address" name="address" rules="required|max:255" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputText :modelValue="field.value" @update:modelValue="handleChange" class="w-full" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label for="address">Địa chỉ <span class="required">(Bắt buộc)</span></label>
                    </FloatLabel>
                    <ErrorMessage name="address" class="p-error" />
                  </Field>
                </div>
              </div>

              <div class="flex flex-wrap gap-4 mt-4">
                <!-- Contact Person -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field v-model="state.model.contact_person" name="contact_person" rules="max:255" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputText :modelValue="field.value" @update:modelValue="handleChange" class="w-full" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label for="contact_person">Người liên hệ</label>
                    </FloatLabel>
                    <ErrorMessage name="contact_person" class="p-error" />
                  </Field>
                </div>

                <!-- Status -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field v-model="state.model.status" name="status" rules="required" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <Select
                        :options="$page.props.data.supplierStatus"
                        optionLabel="label"
                        optionValue="value"
                        :modelValue="field.value"
                        @update:modelValue="handleChange"
                        class="w-full"
                        :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                        showClear
                      />
                      <label for="status">Trạng thái <span class="required">(Bắt buộc)</span></label>
                    </FloatLabel>
                    <ErrorMessage name="status" class="p-error" />
                  </Field>
                </div>
              </div>
              <div class="flex flex-wrap gap-4 mt-4">
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field v-model="state.model.note" name="note" rules="max:255" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <Textarea :modelValue="field.value" @update:modelValue="handleChange" class="w-full" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label for="note">Ghi chú</label>
                    </FloatLabel>
                    <ErrorMessage name="note" class="p-error" />
                  </Field>
                </div>
              </div>
            </div>

            <div class="form-action mt-4">
              <Link :href="$page.props.data.urlBack">
                <Button label="Hủy" icon="pi pi-arrow-left" class="btn-action" />
              </Link>
              <Button label="Lưu" type="submit" icon="pi pi-save" class="btn-action" />
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>

<style scoped>
.form-data {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.form-action {
  margin-top: 20px;
  display: flex;
  gap: 10px;
}
</style>
