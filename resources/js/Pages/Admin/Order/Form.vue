<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, onMounted, reactive, provide, watch, computed, nextTick } from 'vue';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import { required } from '@vee-validate/rules';
import axios from 'axios';
import moment from 'moment';
import $ from 'jquery';
import { useToast } from 'primevue/usetoast';
import { useRequestStore } from '@/store/request';
import { useDirtyForm } from '@/Composables/useDirtyForm';
import { Input } from 'postcss';
import { calcPrice, calcSummary } from '../../../lib/common';

const initialState = ref('');
const isSubmitting = ref(false);
const toast = useToast();
const props = defineProps(['data']);
const page = usePage();
const state = reactive({
  model: {
    order: {},
    order_details: []
  }
});
const initData = () => {
  state.model.order = {
    ...props.data.order
  };
  state.model.order_details = (props.data.orderDetails ?? []).map((item) => ({
    ...item,
    price: item.price !== null ? Number(item.price) : 0,
    quantity: item.quantity !== null ? Number(item.quantity) : 0,
    sub_total: item.sub_total !== null ? Number(item.sub_total) : 0,
    index: 'key_' + (Math.random() * 100000000000000000).toFixed(0)
  }));

  nextTick(() => {
    initialState.value = normalizeState(state.model);
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

onMounted(() => {
  initData();
});

const removeRow = (index) => {
  let rentIndex = state.model.order_details.findIndex((item) => item.index == index);
  if (rentIndex == -1) return;
  state.model.order_details.splice(rentIndex, 1);
};

const addRow = () => {
  if ([1, 2].includes(state.model.order.status)) return;

  state.model.order_details.push({
    id: '',
    order_id: state.model.order.id,
    product_id: null,
    product_name: '',
    price: 0,
    quantity: 1,
    sub_total: 0,
    index: 'key_' + (Math.random() * 100000000000000000).toFixed(0)
  });
};

const calcFee = (data) => {
  let priceRes = calcPrice(data.price, data.quantity, data.sub_total);
  data.price = priceRes.price;
  data.quantity = priceRes.quantity;
  data.sub_total = priceRes.sub_total;
};
watch(
  () => state.model.order_details,
  (newValue) => {
    if (newValue) {
      newValue.forEach((element) => {
        calcFee(element);
      });
    }
  },
  { deep: true }
);
const summaryTotals = () => {
  let summary = calcSummary(state.model.order_details, state.model.order.table_total);
  state.model.order.service_total = summary.service_total;
  state.model.order.table_total = summary.table_total;
  state.model.order.final_total = summary.final_total;
};
watch(
  () => state.model.order_details,
  (newValue) => {
    if (newValue) {
      summaryTotals();
    }
  },
  { deep: true }
);
// Compute totals for summary
// const summaryTotals = computed(() => {
//   const serviceTotal = state.model.order_details.reduce((sum, item) => sum + Number(item.sub_total), 0);
//   const tableTotal = Number(state.model.order.table_total || 0);
//   const finalTotal = serviceTotal + tableTotal;

//   return {
//     tableTotal,
//     serviceTotal,
//     finalTotal
//   };
// });
// watch(
//   summaryTotals,
//   (newTotals) => {
//     state.model.order.service_total = newTotals.serviceTotal;
//     state.model.order.final_total = newTotals.finalTotal;
//   },
//   { deep: true }
// );
watch(
  () => state.model.order_details.map((item) => ({ product_id: item.product_id })),
  (v) => {
    state.model.order_details.forEach((item) => {
      item.price = props.data.productOptions.find((p) => p.value == item.product_id)?.sale_price ?? 0;
    });
  }
);
// Update final_total in model whenever summary totals change

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

const onSubmit = async () => {
  isSubmitting.value = true;
  const submitData = {
    ...state.model.order,
    order_details: state.model.order_details
  };

  useForm(submitData).put(route('admin.order.update', props.data.order.id), {
    onSuccess: () => {
      nextTick(() => {
        initialState.value = normalizeState(state.model);
      });
      toast.add({
        severity: 'success',
        summary: 'Cập nhật thành công',
        life: 3000
      });
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
    onError: (errors) => {
      toast.add({
        severity: 'error',
        summary: 'Có lỗi xảy ra',
        life: 3000
      });
    }
  });
};
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel class="header-form" :header="props.data.title">
        <template #header>
          <div class="flex items-center">
            <span class="font-bold">{{ props.data.title }}</span>
          </div>
        </template>
        <template #icons>
          <Link :href="props.data.urlBack">
            <Button label="Quay lại" icon="pi pi-arrow-left" class="btn-action whitespace-nowrap"></Button>
          </Link>
          <Button label="Lưu thay đổi" type="submit" form="order-form" icon="pi pi-save" class="btn-action ml-2 whitespace-nowrap" :disabled="[1, 2].includes(state.model.order.status)"></Button>
        </template>
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" id="order-form" class="form-data">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="flex flex-col p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl shadow-sm">
                <span class="text-sm font-medium text-blue-600 uppercase tracking-wider">Tiền giờ / Tiền bàn</span>
                <span class="text-2xl font-bold text-gray-800">{{ summaryTotals.tableTotal }}</span>
              </div>
              <div class="flex flex-col p-4 bg-orange-50 border-l-4 border-orange-500 rounded-r-xl shadow-sm">
                <span class="text-sm font-medium text-orange-600 uppercase tracking-wider">Tiền dịch vụ</span>
                <span class="text-2xl font-bold text-gray-800">{{ summaryTotals.serviceTotal }}</span>
              </div>
              <div class="flex flex-col p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-sm">
                <span class="text-sm font-medium text-green-600 uppercase tracking-wider">Tổng thanh toán</span>
                <span class="text-2xl font-bold text-green-700">{{ summaryTotals.finalTotal }}</span>
              </div>
            </div>
            <DataTable :value="state.model.order_details" :tableStyle="{ minWidth: '100%', width: '100%', tableLayout: 'fixed' }" showGridlines scrollHeight="500px">
              <ColumnGroup type="header">
                <Row>
                  <Column style="width: 4%" header="STT"></Column>
                  <Column style="width: 20%" header="Sản phẩm / Dịch vụ"></Column>
                  <Column style="width: 10%" header="Đơn giá"></Column>
                  <Column style="width: 10%" header="Số lượng"></Column>
                  <Column style="width: 10%" header="Thành tiền"></Column>
                  <Column style="width: 5%" v-if="![1, 2].includes(state.model.order.status)"></Column>
                </Row>
              </ColumnGroup>

              <Column>
                <template #body="{ index }">
                  <div class="text-center font-bold text-gray-500">{{ index + 1 }}</div>
                </template>
              </Column>

              <Column>
                <template #body="{ data }">
                  <Field v-if="!data.id && ![1, 2].includes(state.model.order.status)" rules="required" :name="'order_details[' + data.index + '].product_id'" v-model="data.product_id" v-slot="{ field, meta: metaField, handleChange }">
                    <Select
                      class="w-full"
                      :options="$page.props.data.productOptions"
                      optionLabel="label"
                      optionValue="value"
                      :modelValue="field.value"
                      @update:model-value="handleChange"
                      :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                      filter
                      showClear
                    />
                    <ErrorMessage :name="field.name" />
                  </Field>
                  <div v-else>{{ data.product_name }}</div>
                </template>
              </Column>

              <Column>
                <template #body="{ data }">
                  <div class="text-right">{{ data.price }}</div>
                </template>
              </Column>
              <Column>
                <template #body="{ data }">
                  <Field v-if="![1, 2].includes(state.model.order.status)" :name="'order_details[' + data.index + '].quantity'" v-model="data.quantity" v-slot="{ field, meta: metaField, handleChange }">
                    <InputNumber
                      class="w-full"
                      inputClass="text-center"
                      :modelValue="field.value"
                      @update:model-value="handleChange"
                      showButtons
                      buttonLayout="horizontal"
                      decrementButtonIcon="pi pi-minus"
                      incrementButtonIcon="pi pi-plus"
                      :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                    />
                    <ErrorMessage />
                  </Field>
                </template>
              </Column>
              <Column>
                <template #body="{ data }">
                  <Field v-if="![1, 2].includes(state.model.order.status)" :name="'order_details[' + data.index + '].sub_total'" v-model="data.sub_total" v-slot="{ field, meta: metaField, handleChange }">
                    <InputNumber class="w-full" inputClass="text-right" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                  </Field>
                  <div v-else class="text-right">{{ data.sub_total }}</div>
                </template>
              </Column>
              <Column style="width: 80px" v-if="![1, 2].includes(state.model.order.status)">
                <template #body="{ data }">
                  <div class="flex justify-center">
                    <Button icon="pi pi-trash" class="p-button-danger p-button-text p-button-rounded" @click="removeRow(data.index)" />
                  </div>
                </template>
              </Column>
            </DataTable>
            <div v-if="![1, 2].includes(state.model.order.status)" class="flex justify-center mt-6">
              <Button label="Thêm món mới" icon="pi pi-plus" class="p-button-outlined p-button-primary px-6" rounded @click="addRow" />
            </div>
            <div v-if="[1, 2].includes(state.model.order.status)" class="mt-8 p-4 bg-gray-100 rounded-xl text-center border-2 border-dashed border-gray-300">
              <i :class="['pi text-2xl mr-2', state.model.order.status == 1 ? 'pi-check-circle text-green-500' : 'pi-times-circle text-red-500']"></i>
              <span class="text-lg font-bold text-gray-600"> Hóa đơn này {{ state.model.order.status == 1 ? 'đã thanh toán' : 'đã hủy' }} và không thể chỉnh sửa. </span>
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>

<style scoped>
:deep(.p-inputnumber-input) {
  width: 60px !important;
}
</style>
