<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { ref, onMounted, computed, nextTick, reactive } from 'vue';
import Button from 'primevue/button';
import { Form as VeeForm, Field, ErrorMessage, configure, defineRule } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import ja from '@vee-validate/i18n/dist/locale/ja.json';
import $ from 'jquery';
import { getHiragana } from '@/lib/common';
import Textarea from 'primevue/textarea';
import axios from 'axios';
import { useDirtyForm } from '@/Composables/useDirtyForm';

const props = defineProps(['data']);

const initialState = ref('');
const isSubmitting = ref(false);

const state = reactive({
  model: {}
});
const fileInput = ref(null);
const previewUrl = ref(null);

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
  if (props.data.isEdit) {
    state.model = {
      ...props.data.product
    };
  }
  setMessageError();
  nextTick(() => {
    initialState.value = normalizeState(state.model);
  });
});
const setMessageError = () => {
  let messError = {
    en: {
      fields: {
        product_name: {
          required: 'Vui lòng nhập tên sản phẩm',
          max: 'Tên sản phẩm không được vượt quá 255 ký tự'
        },
        sku: {
          required: 'Vui lòng nhập mã sản phẩm',
          max: 'Mã sản phẩm không được vượt quá 255 ký tự'
        },
        supplier_id: {
          required: 'Vui lòng chọn nhà cung cấp'
        },
        price_purchase: {
          required: 'Vui lòng nhập giá nhập',
          max: 'Giá nhập không được vượt quá 255 ký tự'
        },
        price_sale: {
          required: 'Vui lòng nhập giá bán',
          max: 'Giá bán không được vượt quá 255 ký tự'
        },
        quantity: {
          required: 'Vui lòng nhập số lượng',
          max: 'Số lượng không được vượt quá 255 ký tự'
        }
      }
    }
  };
  configure({
    generateMessage: localize(messError)
  });
};
const onSubmit = (values, { setErrors }) => {
  isSubmitting.value = true;
  if (props.data.isEdit) {
    useForm({
      ...state.model,
      _method: 'put'
    }).post(route('admin.product.update', state.model.id), {
      onSuccess: () => {
        nextTick(() => {
          initialState.value = normalizeState(state.model);
        });
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
      onError: (errors) => {
        setErrors(errors);
        onInvalidSubmit({ errors: errors });
      }
    });
  } else {
    useForm(state.model).post(route('admin.product.store'), {
      onSuccess: () => {
        nextTick(() => {
          initialState.value = normalizeState(state.model);
        });
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
      onError: (errors) => {
        setErrors(errors);
        onInvalidSubmit({ errors: errors });
      }
    });
  }
};

const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if ($('[name="' + firstInputError + '"]').hasClass('hidden') || $('[name="' + firstInputError + '"]').attr('type') == 'hidden') {
    ele = $('[name="' + firstInputError + '"]').closest('div');
  }
  if (ele.length) {
    ele.focus();
    $('html, body').animate(
      {
        scrollTop: ele.offset().top - 150 + 'px'
      },
      500
    );
  }
};

const normalizeState = (obj) => {
  return JSON.stringify(obj, (_, value) => {
    if (value === null || value === undefined || value === '') return undefined;
    return value;
  });
};

const hasUnsavedChanges = computed(() => {
  return normalizeState(state.model) !== initialState.value;
});

useDirtyForm(hasUnsavedChanges, isSubmitting);
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel class="header-form" :header="$page.props.data.title">
        <template #header>
          <div class="flex items-center">
            <span class="font-bold">{{ $page.props.data.title }}</span>
          </div>
        </template>
        <template #icons>
          <Button label="Quay lại" icon="pi pi-arrow-left" class="btn-action" @click="$inertia.visit($page.props.data.urlBack)" />
          <Button label="Lưu" type="submit" form="product-form" icon="pi pi-save" class="btn-action ml-2" />
        </template>

        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" id="product-form" class="form-data">
            <div class="flex flex-wrap gap-6">
              <!-- Left Column: Avatar Section -->
              <div class="flex flex-col items-center gap-4 w-1/4">
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
                  <p class="text-sm text-gray-500 mb-2">Ảnh sản phẩm</p>
                  <Button label="Thay đổi ảnh" icon="pi pi-image" class="p-button-outlined p-button-secondary" @click="triggerFileInput" type="button" />
                </div>
              </div>
              <!-- Right Column: Form Fields Section -->
              <div class="flex-1 border rounded-lg p-4 bg-gray-50 w-3/4">
                <div class="flex flex-col gap-4">
                  <div class="flex flex-wrap gap-4">
                    <div class="flex flex-col grow basis-0">
                      <Field name="product_name" rules="required|max:255" v-model="state.model.product_name" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <InputText class="w-full" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                          <label>Tên sản phẩm <span class="required">(Bắt buộc)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                    <div class="flex flex-col grow basis-0">
                      <Field name="sku" rules="required|max:255" v-model="state.model.sku" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <InputText class="w-full" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                          <label>Mã sản phẩm <span class="required">(Bắt buộc)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                  </div>
                  <div class="flex flex-wrap gap-4">
                    <div class="flex flex-col grow basis-0">
                      <Field name="category" rules="required|max:255" v-model="state.model.category" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <Select
                            :options="$page.props.data.productCategories"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                            :modelValue="field.value"
                            @update:model-value="handleChange"
                            :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                          />
                          <label>Danh mục <span class="required">(Bắt buộc)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                    <div class="flex flex-col grow basis-0">
                      <Field name="supplier_id" rules="required|max:255" v-model="state.model.supplier_id" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <Select
                            :options="$page.props.data.supplierOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                            :modelValue="field.value"
                            @update:model-value="handleChange"
                            :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                          />
                          <label :for="field.name">Tên nhà cung cấp <span class="required">(Bắt buộc)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                  </div>
                  <div class="inline-flex flex-wrap gap-4 border rounded p-4 bg-gray-50 w-full">
                    <div class="flex flex-col grow basis-0">
                      <Field name="cost_price" rules="required|max_value:999999999" v-model="state.model.cost_price" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <InputNumber class="w-full" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                          <label>Giá nhập (1 Thùng)</label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                    <div class="flex flex-col grow basis-0">
                      <Field name="quantity" rules="required|max_value:999999" v-model="state.model.quantity" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <InputNumber class="w-full" :name="field.name" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                          <label :for="field.name">Số lượng sản phẩm<span class="required"> (Bắt buộc)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                    <div class="flex flex-col grow basis-0">
                      <Field name="sale_price" rules="required|max_value:999999" v-model="state.model.sale_price" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <InputNumber class="w-full" :name="field.name" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                          <label :for="field.name">Giá bán / 1 sản phẩm <span class="required">(Bắt buộc)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                    <div class="flex flex-col grow basis-0">
                      <Field name="total_amount" rules="required|max_value:999999999" v-model="state.model.total_amount" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <InputNumber class="w-full" :name="field.name" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                          <label :for="field.name">Tổng tiền hàng nhập <span class="required">(Bắt buộc)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
                  </div>
                  <div class="flex flex-wrap gap-4">
                    <div class="flex flex-col grow basis-0">
                      <Field name="description" v-model="state.model.description" v-slot="{ field, meta: metaField, handleChange }">
                        <FloatLabel variant="on">
                          <Textarea class="w-full border" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                          <label>Mô tả</label>
                        </FloatLabel>
                        <ErrorMessage class="p-error" :name="field.name" />
                      </Field>
                    </div>
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

