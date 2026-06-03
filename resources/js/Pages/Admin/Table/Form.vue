1. Khuôn mẫu 
<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, onMounted, reactive, provide, watch, computed, nextTick } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import axios from 'axios';
import moment from 'moment';
import { getErrorCount } from '@/lib/common';
import { useDirtyForm } from '@/Composables/useDirtyForm';

const initialState = ref('');
const isSubmitting = ref(false);
const state = reactive({
  model: {}
});
const props = defineProps(['data']);

onMounted(() => {
  if (props.data.isEdit) {
    state.model = {
      ...props.data.table
    };
  }
  setMessageError();
  nextTick(() => {
    initialState.value = normalizeState(state.model);
  });
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
const setMessageError = () => {
  let messError = {
    en: {
      fields: {
        table_name: {
          required: 'Tên bàn không được để trống',
          max: 'Tên bàn không được vượt quá 255 ký tự'
        },
        table_price_id: {
          required: 'Giá bàn không được để trống'
        },
        status: {
          required: 'Trạng thái không được để trống'
        }
      }
    }
  };
  configure({
    generateMessage: localize(messError)
  });
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

const onSubmit = () => {
  isSubmitting.value = true;
  let convertModel = {
    ...state.model
  };

  if (props.data.isEdit) {
    convertModel['_method'] = 'PUT';
    useForm(convertModel).post(route('admin.table.update', props.data.table.id), {
      onSuccess: () => {
        nextTick(() => {
          initialState.value = normalizeState(state.model);
        });
      },
      onFinish: () => {
        isSubmitting.value = false;
      }
    });
    return;
  }
  useForm(convertModel).post(route('admin.table.store'), {
    onSuccess: () => {
      nextTick(() => {
        initialState.value = normalizeState(state.model);
      });
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};
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
          <Link :href="$page.props.data.urlBack">
            <Button label="Quay lại" icon="pi pi-arrow-left" class="btn-action"></Button>
          </Link>
          <Button label="Lưu" type="submit" form="#-form" icon="pi pi-save" class="btn-action ml-2"></Button>
        </template>
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" id="#-form" class="form-data">
            <div class="card flex flex-col gap-4">
              <div class="flex flex-wrap gap-4">
                <div class="flex flex-col gap-2 basis-0 grow min-w-[150]">
                  <Field name="table_name" rules="required|max:255" v-model="state.model.table_name" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputText class="w-full" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label :for="field.name"> Tên bàn <span class="required">(Bắt buộc)</span></label>
                    </FloatLabel>
                  </Field>
                  <ErrorMessage name="table_name" class="text-red-500" />
                </div>
                <div class="flex flex-col gap-2 basis-0 grow min-w-[150]">
                  <Field name="table_price_id" rules="required" v-model="state.model.table_price_id" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <Select
                        class="w-full"
                        :options="$page.props.data.tablePrices"
                        optionLabel="label"
                        optionValue="value"
                        :modelValue="field.value"
                        @update:model-value="handleChange"
                        filter
                        show-clear
                        :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                      />
                      <label :for="field.name">Giá bàn <span class="required">(Bắt buộc)</span></label>
                    </FloatLabel>
                  </Field>
                  <ErrorMessage name="table_price_id" class="text-red-500" />
                </div>
                <div class="flex flex-col gap-2 basis-0 grow min-w-[150]">
                  <Field name="status" rules="required" v-model="state.model.status" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <Select
                        class="w-full"
                        :options="$page.props.data.tableStatus"
                        optionLabel="label"
                        optionValue="value"
                        :modelValue="field.value"
                        @update:model-value="handleChange"
                        :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                        filter
                        show-clear
                      />
                      <label :for="field.name">Trạng thái <span class="required">(Bắt buộc)</span></label>
                    </FloatLabel>
                  </Field>
                  <ErrorMessage name="status" class="text-red-500" />
                </div>
              </div>
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>


