<script setup>
import { reactive, ref, watch, computed, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import CustomerSearchModal from '@/Components/Billiard/CustomerSearchModal.vue';

const page = usePage();

const props = defineProps({
  visible: { type: Boolean, default: false },
  request: { type: Object, default: () => ({}) }
});
const showCustomerModal = ref(false);
const openCustomerModal = () => {
  showCustomerModal.value = true;
};
const emit = defineEmits(['update:visible', 'update:isSubmitting']);
const localVisible = ref(props.visible);

const isStudent = ref(false);
const isPrint = ref(false);

watch(
  () => props.visible,
  (v) => {
    localVisible.value = v;
    if (v) {
      state.model = {
        ...props.request,
        payment_method: props.request.payment_method || 1,
        created_by: props.request.created_by || page.props.value.user?.id,
        customer_paid: props.request.customer_paid || 0
      };
      isStudent.value = false;
      isPrint.value = false;
    }
  }
);
watch(localVisible, (v) => emit('update:visible', v));
const state = reactive({
  model: {}
});

onMounted(() => {
  state.model = {
    ...props.request,
    payment_method: props.request.payment_method || 1,
    created_by: props.request.created_by || page.props.value.user?.id,
    customer_paid: props.request.customer_paid || 0
  };
  isStudent.value = false;
  isPrint.value = false;
});

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
      product_id: item.product_id,
      item_name: item.product_name,
      quantity: item.quantity,
      price: item.price,
      sub_total: item.sub_total,
      discount: 0,
      total_amount: item.sub_total
    }));
  }
  if (state.model.order && state.model.order.table_total > 0) {
    details.push({
      product_id: null,
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

const discount = computed(() => {
  if (isStudent.value) {
    return Math.round((state.model.order?.table_total || 0) * 0.1);
  }
  return 0;
});

const finalTotal = computed(() => {
  const tableTotal = state.model.order?.table_total || 0;
  const serviceTotal = state.model.order?.service_total || 0;
  return Math.max(0, tableTotal + serviceTotal - discount.value);
});

const qrCodeDescription = computed(() => {
  const orderNumber = state.model.order?.order_number || '';
  return `Thanh toan don hang ${orderNumber}`.trim();
});

const qrCodeUrl = computed(() => {
  const bankId = page.props.value.vietqr?.bank_id || 'MB';
  const accountNo = page.props.value.vietqr?.account_no || '0356166166';
  const accountName = encodeURIComponent(page.props.value.vietqr?.account_name || 'BILLIARD CLUB');
  const template = page.props.value.vietqr?.template || 'qr_only';

  const amount = Math.round(finalTotal.value || 0);
  const description = encodeURIComponent(qrCodeDescription.value);

  return `https://img.vietqr.io/image/${bankId}-${accountNo}-${template}.png?amount=${amount}&addInfo=${description}&accountName=${accountName}`;
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

const isSubmitting = ref(false);

const onSubmit = () => {
  isSubmitting.value = true;
  emit('update:isSubmitting', true);
  const payload = {
    ...state.model.order,
    order_id: state.model.order?.id,
    customer_id: state.model.customer_id,
    payment_method: state.model.payment_method,
    table_name: state.model.order?.table?.table_name,
    details: invoiceDetails.value,
    discount: discount.value,
    final_total: finalTotal.value
  };

  useForm(payload).post(route('admin.invoice.store'), {
    preserveScroll: true,
    onSuccess: (pageRes) => {
      emit('update:visible', false);
      if (isPrint.value && pageRes.props.dataSession?.urlRedirect) {
        const invoiceId = pageRes.props.dataSession.urlRedirect;
        window.open(route('admin.invoice.print', { invoice: invoiceId }), '_blank');
      }
    },
    onFinish: () => {
      isSubmitting.value = false;
      emit('update:isSubmitting', false);
    }
  });
};
const onInvalidSubmit = ({ errors }) => {};

const clearFilter = () => {
  state.model = {};
};

const handleSelectCustomer = (customer) => {
  state.model.customer_id = customer.id;

  // Check if customer exists in options to ensure label is displayed
  const customerOptions = page.props.value.data.customerOptions || [];
  const exists = customerOptions.some((opt) => opt.value == customer.id);

  if (!exists) {
    customerOptions.push({
      label: customer.name,
      value: customer.id
    });
  }
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
                <Select
                  :disabled="$page.props.user.user_role != 1"
                  :options="$page.props.data.userOptions"
                  optionLabel="label"
                  optionValue="value"
                  :modelValue="field.value"
                  @update:model-value="handleChange"
                  :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                  class="w-full"
                />
              </Field>
            </div>
            <div class="flex flex-col gap-1 basis-0 grow">
              <span class="text-sm text-gray-500 font-medium">Khách hàng</span>
              <Field name="customer_id" v-model="state.model.customer_id" v-slot="{ field, meta: metaField, handleChange }">
                <Select
                  :options="$page.props.data.customerOptions"
                  optionLabel="label"
                  optionValue="value"
                  :modelValue="field.value"
                  @update:model-value="handleChange"
                  :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                  class="w-full sel-search"
                  filter
                >
                  <template #dropdownicon>
                    <Button label="Chọn" class="w-[100px] pr-1" severity="secondary" variant="text" size="small" icon="pi pi-search" @click.stop="openCustomerModal" />
                  </template>
                </Select>
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
              <span class="font-bold text-gray-700">Tùy chọn hóa đơn</span>
              <div class="flex flex-col gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 mb-2">
                <div class="flex items-center gap-3">
                  <Checkbox id="chk-student" v-model="isStudent" :binary="true" />
                  <label for="chk-student" class="text-sm font-semibold text-gray-700 cursor-pointer select-none"> Học sinh, sinh viên (Giảm 10% tiền bàn) </label>
                </div>
                <div class="flex items-center gap-3">
                  <Checkbox id="chk-print" v-model="isPrint" :binary="true" />
                  <label for="chk-print" class="text-sm font-semibold text-gray-700 cursor-pointer select-none"> In hóa đơn sau khi lưu </label>
                </div>
              </div>

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
                    {{ formatCurrency(Math.max(0, (state.model.customer_paid || 0) - finalTotal)) }}
                  </div>
                </div>
              </div>

              <div v-if="state.model.payment_method === 2" class="mt-4 flex flex-col items-center justify-center p-6 border border-dashed border-gray-300 rounded-xl bg-gray-50 w-full">
                <div class="relative group">
                  <img :src="qrCodeUrl" alt="QR Code" class="w-48 h-48 rounded-lg shadow-md bg-white p-2 border border-gray-200 transition-transform duration-300 group-hover:scale-105" />
                  <div class="absolute -bottom-2 -right-2 bg-blue-600 text-white rounded-full p-1.5 shadow-md flex items-center justify-center">
                    <i class="pi pi-qrcode text-xs"></i>
                  </div>
                </div>
                <span class="mt-4 text-xs text-gray-650 font-bold uppercase tracking-wider">Quét mã QR để chuyển khoản</span>

                <div class="w-full mt-4 p-4 bg-white rounded-lg border border-gray-250 text-xs flex flex-col gap-2">
                  <div class="flex justify-between border-b pb-1.5 border-gray-100">
                    <span class="text-gray-500">Ngân hàng:</span>
                    <span class="font-bold text-gray-800">{{ $page.props.vietqr?.bank_id || 'MB' }}</span>
                  </div>
                  <div class="flex justify-between border-b pb-1.5 border-gray-100">
                    <span class="text-gray-500">Số tài khoản:</span>
                    <span class="font-bold text-gray-800 copy-field select-all cursor-pointer hover:text-blue-600" title="Click để copy">{{ $page.props.vietqr?.account_no || '0356166166' }}</span>
                  </div>
                  <div class="flex justify-between border-b pb-1.5 border-gray-100">
                    <span class="text-gray-500">Chủ tài khoản:</span>
                    <span class="font-bold text-gray-800">{{ $page.props.vietqr?.account_name || 'BILLIARD CLUB' }}</span>
                  </div>
                  <div class="flex justify-between border-b pb-1.5 border-gray-100">
                    <span class="text-gray-500">Số tiền:</span>
                    <span class="font-bold text-blue-600">{{ formatCurrency(finalTotal) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Nội dung CK:</span>
                    <span class="font-bold text-amber-600 select-all cursor-pointer hover:text-blue-600" title="Click để copy">{{ qrCodeDescription }}</span>
                  </div>
                </div>
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
              <div v-if="discount > 0" class="flex justify-between items-center text-red-600 font-bold transition-all duration-300">
                <span class="text-sm">Giảm giá HSSV (10% tiền bàn):</span>
                <span>-{{ formatCurrency(discount) }}</span>
              </div>
              <div class="flex justify-between items-center mt-2 pt-2 border-t-2 border-dashed border-gray-200">
                <span class="font-black text-lg text-blue-800">Tổng cộng:</span>
                <span class="font-black text-2xl text-blue-600">{{ formatCurrency(finalTotal) }}</span>
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
  <CustomerSearchModal v-model:visible="showCustomerModal" @select="handleSelectCustomer" :currentId="state.model.customer_id" />
</template>
