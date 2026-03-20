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
      ...props.data.table,
      rooms: props.data.building.rooms ? props.data.building.rooms.map((i) => ({ ...i, index: 'key_' + (Math.random() * 100000000000000000).toFixed(0) })) : []
    };
  }
  setMessageError();
  nextTick(() => {
    initialState.value = normalizeState(state.model);
  });
});
defineRule('unique_code', (value) => {
  if (value == '' || value == null || value == undefined || value == 'undefined') {
    return true;
  }
  return axios
    .post(route('admin.table.checkCode'), {
      _token: Laravel.csrfToken,
      value: value,
      id: props.data.table.id
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
  useForm(convertModel).post(route('admin.#.store'), {
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
          <form @submit="handleSubmit($event, onSubmit)" id="#-form" class="form-data"></form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>


