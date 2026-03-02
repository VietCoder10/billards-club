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
  if (props.data.isEdit && props.data.supplier) {
    state.model = props.data.supplier;
  }
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

// Validation messages
const messError = {
  en: {
    fields: {
      supplier_name: {
        required: 'Supplier Name is required.',
        max: 'Supplier Name cannot exceed 255 characters.'
      },
      email: {
        required: 'Email is required.',
        email: 'Please enter a valid email.',
        unique_email: 'This email is already registered.'
      },
      phone: {
        required: 'Phone is required.',
        max: 'Phone number cannot exceed 20 characters.'
      },
      address: {
        required: 'Address is required.',
        max: 'Address cannot exceed 255 characters.'
      },
      contact_person: {
        max: 'Contact Person cannot exceed 255 characters.'
      }
    }
  }
};
configure({ generateMessage: localize(messError) });

const statusOptions = [
  { label: 'Active', value: '1' },
  { label: 'Inactive', value: '0' }
];
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
                  <Field name="supplier_name" rules="required|max:255" v-model="state.model.supplier_name" v-slot="{ field, meta, handleChange }">
                    <FloatLabel variant="on">
                      <InputText v-model="state.model.supplier_name" v-bind="field" class="w-full" :class="{ 'p-invalid': !meta.valid && meta.touched }" v-on:update:model-value="handleChange" />
                      <label for="supplier_name">Supplier Name <span class="required">(required)</span></label>
                    </FloatLabel>
                    <ErrorMessage name="supplier_name" class="p-error" />
                  </Field>
                </div>

                <!-- Email -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field name="email" :rules="flagValidateUnique ? 'required|email|max:255' : 'required|email|max:255'" v-model="state.model.email" v-slot="{ field, meta, handleChange }">
                    <FloatLabel variant="on">
                      <InputText
                        v-model="state.model.email"
                        v-bind="field"
                        class="w-full"
                        :class="{ 'p-invalid': !meta.valid && meta.touched }"
                        @keypress="flagValidateUnique = false"
                        @blur="flagValidateUnique = true"
                        v-on:update:model-value="handleChange"
                      />
                      <label for="email">Email</label>
                    </FloatLabel>
                    <ErrorMessage name="email" class="p-error" />
                  </Field>
                </div>
              </div>

              <div class="flex flex-wrap gap-4 mt-4">
                <!-- Phone -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field name="phone" rules="required|max:20" v-model="state.model.phone" v-slot="{ field, meta, handleChange }">
                    <FloatLabel variant="on">
                      <InputText v-model="state.model.phone" v-bind="field" class="w-full" :class="{ 'p-invalid': !meta.valid && meta.touched }" v-on:update:model-value="handleChange" />
                      <label for="phone">Phone <span class="required">(required)</span></label>
                    </FloatLabel>
                    <ErrorMessage name="phone" class="p-error" />
                  </Field>
                </div>

                <!-- Address -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field name="address" rules="required|max:255" v-model="state.model.address" v-slot="{ field, meta, handleChange }">
                    <FloatLabel variant="on">
                      <InputText v-model="state.model.address" v-bind="field" class="w-full" :class="{ 'p-invalid': !meta.valid && meta.touched }" v-on:update:model-value="handleChange" />
                      <label for="address">Address <span class="required">(required)</span></label>
                    </FloatLabel>
                    <ErrorMessage name="address" class="p-error" />
                  </Field>
                </div>
              </div>

              <div class="flex flex-wrap gap-4 mt-4">
                <!-- Contact Person -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field name="contact_person" rules="max:255" v-model="state.model.contact_person" v-slot="{ field, meta, handleChange }">
                    <FloatLabel variant="on">
                      <InputText v-model="state.model.contact_person" v-bind="field" class="w-full" :class="{ 'p-invalid': !meta.valid && meta.touched }" v-on:update:model-value="handleChange" />
                      <label for="contact_person">Contact Person</label>
                    </FloatLabel>
                    <ErrorMessage name="contact_person" class="p-error" />
                  </Field>
                </div>

                <!-- Status -->
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field name="status" v-slot="{ field }">
                    <FloatLabel variant="on">
                      <Dropdown v-model="state.model.status" :options="statusOptions" optionLabel="label" optionValue="value" v-bind="field" class="w-full" />
                      <label for="status">Status</label>
                    </FloatLabel>
                  </Field>
                </div>
              </div>
              <div class="flex flex-wrap gap-4 mt-4">
                <div class="flex flex-col grow basis-0 gap-2">
                  <Field name="note" rules="max:255" v-model="state.model.note" v-slot="{ field, meta, handleChange }">
                    <FloatLabel variant="on">
                      <Textarea v-model="state.model.note" v-bind="field" class="w-full" :class="{ 'p-invalid': !meta.valid && meta.touched }" v-on:update:model-value="handleChange" />
                      <label for="note">Note</label>
                    </FloatLabel>
                    <ErrorMessage name="note" class="p-error" />
                  </Field>
                </div>
              </div>
            </div>

            <div class="form-action mt-4">
              <Link :href="$page.props.data.urlBack">
                <Button label="Cancel" icon="pi pi-arrow-left" class="btn-action" />
              </Link>
              <Button label="Save" type="submit" icon="pi pi-save" class="btn-action" />
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
