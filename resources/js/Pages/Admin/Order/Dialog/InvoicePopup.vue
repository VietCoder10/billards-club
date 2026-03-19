<script setup>
import { reactive, ref, watch, computed } from 'vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { useForm, usePage } from '@inertiajs/inertia-vue3';

const page = usePage();

const props = defineProps({
  visible: { type: Boolean, default: false },
  request: { type: Object, default: () => ({}) }
});
const emit = defineEmits(['update:visible']);
const localVisible = ref(props.visible);
watch(
  () => props.visible,
  (v) => (localVisible.value = v)
);
watch(localVisible, (v) => emit('update:visible', v));
const state = reactive({
  model: {
    ...props.request,
    payment_method: props.request.payment_method || 1,
    created_by: props.request.created_by || page.props.value.user?.id,
    customer_paid: props.request.customer_paid || 0
  }
});

// Watch for changes in request prop to update state
watch(
  () => props.request,
  (newVal) => {
    state.model = {
      ...newVal,
      payment_method: newVal.payment_method || 1,
      created_by: newVal.created_by || page.props.value.user?.id,
      customer_paid: newVal.customer_paid || 0
    };
  },
  { deep: true }
);

const invoiceDetails = computed(() => {
  let details = [];
  if (state.model.order_details) {
    details = state.model.order_details.map((item) => ({
      item_name: item.product_name,
      quantity: item.quantity,
      price: item.price,
      sub_total: item.sub_total,
      discount: 0,
      total_amount: item.sub_total
    }));
  }

  // Thêm tiền bàn vào chi tiết hóa đơn
  if (state.model.order && state.model.order.table_total > 0) {
    details.push({
      item_name: 'Tiền bàn (' + (state.model.order.total_minutes || 0) + ' phút)',
      quantity: 1,
      price: state.model.order.table_total,
      sub_total: state.model.order.table_total,
      discount: 0,
      total_amount: state.model.order.table_total
    });
  }
  return details;
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

const isSubmitting = ref(false);

const onSubmit = () => {
  isSubmitting.value = true;
  const payload = {
    invoice_number: state.model.order?.order_number,
    table_name: state.model.order?.table?.table_name,
    table_total: state.model.order?.table_total || 0,
    service_total: state.model.order?.service_total || 0,
    total_amount: state.model.order?.final_total || 0,
    discount: state.model.order?.discount || 0,
    final_amount: state.model.order?.final_total || 0,
    payment_method: state.model.payment_method || 1, // Mặc định 1: tiền mặt
    customer_paid: state.model.payment_method === 1 ? state.model.customer_paid : 0,
    created_by: state.model.created_by,
    details: invoiceDetails.value
  };

  useForm(payload).post(route('admin.invoice.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('update:visible', false);
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};
const onInvalidSubmit = ({ errors }) => {};

const clearFilter = () => {
  state.model = {};
};
</script>
<template>
  <Dialog v-model:visible="localVisible" header="Thanh toán" modal :style="{ width: '80vw' }">
    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)" id="invoice-form" class="form-data">
        <div class="card flex flex-col gap-4">
          <div class="flex flex-wrap gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex flex-col gap-1 basis-0 grow">
              <span class="text-sm text-gray-500 font-medium">Mã đơn hàng</span>
              <span class="font-bold text-gray-800">{{ state.model.order?.order_number }}</span>
            </div>
            <div class="flex flex-col gap-1 basis-0 grow">
              <span class="text-sm text-gray-500 font-medium">Bàn</span>
              <span class="font-bold text-gray-800">{{ state.model.order?.table?.table_name }}</span>
            </div>
            <div class="flex flex-col gap-1 basis-0 grow">
              <span class="text-sm text-gray-500 font-medium">Nhân viên tạo HĐ</span>
              <Field name="created_by" v-model="state.model.created_by" v-slot="{ field, meta: metaField, handleChange }">
                <Select :options="$page.props.data.userOptions" optionLabel="label" optionValue="value" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" class="w-full" />
              </Field>
            </div>
          </div>

          <div class="flex flex-wrap gap-4">
            <DataTable class="w-full tbl-form" :value="invoiceDetails" showGridlines>
              <ColumnGroup type="header">
                <Row>
                  <Column header="Tên sản phẩm" />
                  <Column header="Số lượng" />
                  <Column header="Đơn giá" />
                  <Column header="Thành tiền" />
                </Row>
              </ColumnGroup>
              <Column class="text-left font-semibold text-gray-700">
                <template #body="{ data }">
                  {{ data.item_name }}
                </template>
              </Column>
              <Column class="text-center">
                <template #body="{ data }">
                  {{ data.quantity }}
                </template>
              </Column>
              <Column class="text-right">
                <template #body="{ data }">
                  {{ formatCurrency(data.price) }}
                </template>
              </Column>
              <Column class="text-right font-black text-gray-800">
                <template #body="{ data }">
                  {{ formatCurrency(data.total_amount) }}
                </template>
              </Column>
            </DataTable>
          </div>

          <Divider />

          <div class="flex flex-wrap gap-4">
            <div class="flex flex-col gap-3 w-1/2">
              <span class="font-bold text-gray-700">Phương thức thanh toán</span>
              <div class="flex gap-4">
                <div
                  @click="state.model.payment_method = 1"
                  :class="[
                    'flex-1 p-3 rounded-lg border-2 cursor-pointer transition-all text-center font-semibold',
                    state.model.payment_method === 1 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-blue-200 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  <i class="pi pi-money-bill block text-2xl mb-2 text-green-500"></i>
                  Tiền mặt
                </div>
                <div
                  @click="state.model.payment_method = 2"
                  :class="[
                    'flex-1 p-3 rounded-lg border-2 cursor-pointer transition-all text-center font-semibold',
                    state.model.payment_method === 2 ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-blue-200 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  <i class="pi pi-qrcode block text-2xl mb-2 text-blue-500"></i>
                  Chuyển khoản
                </div>
              </div>

              <div v-if="state.model.payment_method === 1" class="mt-4 flex flex-col gap-3">
                <div class="flex flex-col gap-1">
                  <span class="text-sm font-bold text-gray-700">Tiền khách đưa</span>
                  <InputNumber v-model="state.model.customer_paid" mode="currency" currency="VND" locale="vi-VN" class="w-full" :min="0" />
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-sm font-bold text-gray-700">Tiền thừa</span>
                  <div class="p-inputtext p-component bg-gray-100 font-bold text-blue-600 flex items-center h-10 px-3 cursor-not-allowed">
                    {{ formatCurrency(Math.max(0, (state.model.customer_paid || 0) - (state.model.order?.final_total || 0))) }}
                  </div>
                </div>
              </div>

              <div v-if="state.model.payment_method === 2" class="mt-4 flex flex-col items-center justify-center p-4 border border-dashed border-gray-300 rounded-xl bg-gray-50">
                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=ChuyenKhoan_TongTien_' + (state.model.order?.final_total || 0)" alt="QR Code" class="w-48 h-48 rounded-lg shadow-sm bg-white p-2 border" />
                <span class="mt-3 text-sm text-gray-500 font-medium">Quét mã QR để thanh toán</span>
              </div>
            </div>

            <div class="flex flex-col gap-2 w-1/2 max-w-sm ml-auto">
              <div class="flex justify-between items-center text-gray-600">
                <span class="font-medium text-sm">Tiền dịch vụ:</span>
                <span class="font-bold">{{ formatCurrency(state.model.order?.service_total) }}</span>
              </div>
              <div class="flex justify-between items-center text-gray-600">
                <span class="font-medium text-sm">Tiền bàn:</span>
                <span class="font-bold">{{ formatCurrency(state.model.order?.table_total) }}</span>
              </div>
              <div class="flex justify-between items-center mt-2 pt-2 border-t-2 border-dashed border-gray-200">
                <span class="font-black text-lg text-blue-800">Tổng cộng:</span>
                <span class="font-black text-2xl text-blue-600">{{ formatCurrency(state.model.order?.final_total) }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-4">
            <Button type="button" label="Hủy" icon="pi pi-times" class="p-button-text p-button-secondary" @click="localVisible = false" />
            <Button type="submit" label="Lưu hóa đơn" icon="pi pi-check" class="p-button-primary" :loading="isSubmitting" />
          </div>
        </div>
      </form>
    </VeeForm>
  </Dialog>
</template>
