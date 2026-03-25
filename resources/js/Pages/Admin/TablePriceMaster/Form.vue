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
      ...props.data.tablePrice
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
    ja: {
      fields: {}
    }
  };
  configure({
    generateMessage: localize(messError)
  });
};
setLocale('ja');

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
    useForm(convertModel).post(route('admin.table_price_master.update', { table_price_master: state.model.id }), {
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
  useForm(convertModel).post(route('admin.table_price_master.store'), {
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
                  <Field name="price_name" v-model="state.model.price_name" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputText class="w-full" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label :for="field.name">Tên loại bàn</label>
                    </FloatLabel>
                  </Field>
                  <ErrorMessage name="price_name" class="text-red-500" />
                </div>
                <div class="flex flex-col gap-2 basis-0 grow min-w-[150]">
                  <Field name="price_per_hour" v-model="state.model.price_per_hour" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <InputNumber class="w-full" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                      <label :for="field.name">Giá/giờ</label>
                    </FloatLabel>
                  </Field>
                  <ErrorMessage name="price_per_hour" class="text-red-500" />
                </div>
              </div>
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>


