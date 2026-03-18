<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, onMounted, onBeforeUnmount, reactive, provide, watch, computed, nextTick } from 'vue';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import { required } from '@vee-validate/rules';
import axios from 'axios';
import moment from 'moment';
import $ from 'jquery';
import { useToast } from 'primevue/usetoast';
import { useRequestStore } from '@/store/request';
import { useDirtyForm } from '@/Composables/useDirtyForm';
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

const isPending = computed(() => state.model.order.status === 1);

const calculateTotalMinutes = () => {
  if (!state.model.order.started_at) return;
  const start = moment(state.model.order.started_at);
  const end = isPending.value ? moment() : moment(state.model.order.ended_at || undefined);
  state.model.order.total_minutes = Math.max(0, end.diff(start, 'minutes'));
};

const updateTableTotal = () => {
  const minutes = Number(state.model.order.total_minutes || 0);
  const pricePerHour = Number(state.model.order.price_per_hour || 0);
  state.model.order.table_total = (minutes / 60) * pricePerHour;
};

let timer = null;

const initData = () => {
  state.model.order = {
    ...props.data.order,
    price_per_hour: props.data.order.price_per_hour ? Number(props.data.order.price_per_hour) : 0,
    total_minutes: props.data.order.total_minutes ? Number(props.data.order.total_minutes) : 0,
    table_total: props.data.order.table_total ? Number(props.data.order.table_total) : 0,
    service_total: props.data.order.service_total ? Number(props.data.order.service_total) : 0,
    final_total: props.data.order.final_total ? Number(props.data.order.final_total) : 0
  };
  state.model.order_details = (props.data.order.details ?? []).map((item) => ({
    ...item,
    price: item.price !== null ? Number(item.price) : 0,
    quantity: item.quantity !== null ? Number(item.quantity) : 0,
    sub_total: item.sub_total !== null ? Number(item.sub_total) : 0,
    index: 'key_' + (Math.random() * 100000000000000000).toFixed(0)
  }));

  calculateTotalMinutes();
  updateTableTotal();

  if (isPending.value) {
    timer = setInterval(() => {
      calculateTotalMinutes();
    }, 1000 * 30); // Update every 30 seconds
  }

  nextTick(() => {
    initialState.value = normalizeState(state.model);
  });
};

onBeforeUnmount(() => {
  if (timer) clearInterval(timer);
});

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
  if ([2, 3].includes(state.model.order.status)) return;

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
  updateTableTotal();
  let summary = calcSummary(state.model.order_details, state.model.order.table_total);
  state.model.order.service_total = summary.service_total;
  state.model.order.final_total = summary.final_total;
};

watch(
  () => [state.model.order_details, state.model.order.total_minutes, state.model.order.price_per_hour],
  () => {
    summaryTotals();
  },
  { deep: true }
);

watch(
  () => state.model.order_details.map((item) => ({ product_id: item.product_id })),
  (v) => {
    const order_details = state.model.order_details;
    const seen = {};
    const toRemove = [];

    order_details.forEach((item, index) => {
      const product = props.data.productOptions.find((p) => p.value == item.product_id);
      if (product) {
        item.price = Number(product.sale_price || 0);
        item.product_name = product.label;
      }

      if (item.product_id) {
        if (seen[item.product_id] !== undefined) {
          const prevIndex = seen[item.product_id];
          order_details[prevIndex].quantity += item.quantity;

          // Keep the ID if the current item has one but the previous one doesn't
          if (item.id && !order_details[prevIndex].id) {
            order_details[prevIndex].id = item.id;
          }

          toRemove.push(index);
        } else {
          seen[item.product_id] = index;
        }
      }
    });

    // Remove duplicates from end to start
    for (let i = toRemove.length - 1; i >= 0; i--) {
      order_details.splice(toRemove[i], 1);
    }
  },
  { deep: true }
);

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
    preserveState: false,
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

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel class="header-form mb-4" :header="props.data.title">
        <template #header>
          <div class="flex items-center gap-3">
            <span class="font-bold text-xl">{{ props.data.title }}</span>
            <div v-if="isPending" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold animate-pulse">• ĐANG PHỤC VỤ</div>
          </div>
        </template>
        <template #icons>
          <Link :href="props.data.urlBack">
            <Button label="Quay lại" icon="pi pi-arrow-left" class="p-button-outlined p-button-secondary whitespace-nowrap"></Button>
          </Link>
          <Button label="Lưu thay đổi" type="submit" form="order-form" icon="pi pi-save" class="p-button-primary ml-2 whitespace-nowrap" :disabled="[2, 3].includes(state.model.order.status)"></Button>
        </template>

        <div class="mb-6 p-4 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-8">
          <div class="flex flex-col">
            <span class="text-xs text-gray-500 uppercase font-semibold">Bàn</span>
            <span class="text-lg font-bold text-gray-800">{{ state.model.order.table?.table_name }}</span>
          </div>
          <div class="flex flex-col">
            <span class="text-xs text-gray-500 uppercase font-semibold">Mã đơn hàng</span>
            <span class="text-lg font-mono font-bold text-gray-800">#{{ state.model.order.order_number }}</span>
          </div>
          <div class="flex flex-col">
            <span class="text-xs text-gray-500 uppercase font-semibold">Bắt đầu lúc</span>
            <span class="text-lg font-bold text-gray-800">{{ moment(state.model.order.started_at).format('HH:mm - DD/MM/YYYY') }}</span>
          </div>
          <div v-if="!isPending" class="flex flex-col">
            <span class="text-xs text-gray-500 uppercase font-semibold">Kết thúc lúc</span>
            <span class="text-lg font-bold text-gray-800">{{ moment(state.model.order.ended_at).format('HH:mm - DD/MM/YYYY') }}</span>
          </div>
        </div>

        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" id="order-form" class="form-data">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
              <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 border-1 border-blue-200 rounded-2xl shadow-sm">
                <div class="text-sm font-bold text-blue-600 uppercase mb-3 flex items-center gap-2"><i class="pi pi-clock"></i> Tiền Giờ</div>
                <div class="flex flex-col gap-3">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Đơn giá/h:</span>
                    <Field name="price_per_hour" v-model="state.model.order.price_per_hour" v-slot="{ field, meta: metaField, handleChange }">
                      <InputNumber inputClass="text-right font-bold w-32" mode="currency" currency="VND" locale="vi-VN" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                    </Field>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Số phút:</span>
                    <Field name="total_minutes" v-model="state.model.order.total_minutes" v-slot="{ field, meta: metaField, handleChange }">
                      <InputNumber inputClass="text-right font-bold w-32" :suffix="' Phút'" :disabled="isPending" :modelValue="field.value" @update:model-value="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                    </Field>
                  </div>
                  <hr class="border-blue-200" />
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-blue-800">Cộng tiền giờ:</span>
                    <span class="text-xl font-black text-blue-900">{{ formatCurrency(state.model.order.table_total) }}</span>
                  </div>
                </div>
              </div>

              <div class="p-4 bg-gradient-to-br from-orange-50 to-amber-50 border-1 border-orange-200 rounded-2xl shadow-sm">
                <div class="text-sm font-bold text-orange-600 uppercase mb-3 flex items-center gap-2"><i class="pi pi-shopping-bag"></i> Dịch Vụ</div>
                <div class="flex flex-col justify-center h-full pb-4">
                  <div class="flex items-center justify-between h-full">
                    <span class="font-bold text-orange-800">Cộng dịch vụ:</span>
                    <span class="text-3xl font-black text-orange-900">{{ formatCurrency(state.model.order.service_total) }}</span>
                  </div>
                </div>
              </div>

              <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 border-1 border-green-200 rounded-2xl shadow-sm">
                <div class="text-sm font-bold text-green-600 uppercase mb-3 flex items-center gap-2"><i class="pi pi-wallet"></i> Tổng Thanh Toán</div>
                <div class="flex flex-col justify-center h-full pb-4">
                  <div class="flex items-center justify-between h-full">
                    <span class="font-bold text-green-800 text-lg">Tổng cộng:</span>
                    <span class="text-4xl font-black text-green-900">{{ formatCurrency(state.model.order.final_total) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
              <DataTable :value="state.model.order_details" :tableStyle="{ minWidth: '100%', width: '100%', tableLayout: 'fixed' }" showGridlines>
                <ColumnGroup type="header">
                  <Row>
                    <Column style="width: 5%" header="STT"></Column>
                    <Column style="width: 30%" header="Sản phẩm / Dịch vụ"></Column>
                    <Column style="width: 10%" header="Đơn giá"></Column>
                    <Column style="width: 15%" header="Số lượng"></Column>
                    <Column style="width: 15%" header="Thành tiền"></Column>
                    <Column style="width: 6%" v-if="![2, 3].includes(state.model.order.status)"></Column>
                  </Row>
                </ColumnGroup>

                <Column>
                  <template #body="{ index }">
                    <div class="text-center font-bold text-gray-400">{{ index + 1 }}</div>
                  </template>
                </Column>

                <Column>
                  <template #body="{ data }">
                    <Field v-if="!data.id && ![2, 3].includes(state.model.order.status)" rules="required" :name="'order_details[' + data.index + '].product_id'" v-model="data.product_id" v-slot="{ field, meta: metaField, handleChange }">
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
                        placeholder="Chọn sản phẩm..."
                      />
                    </Field>
                    <div v-else class="font-semibold text-gray-700">{{ data.product_name }}</div>
                  </template>
                </Column>

                <Column>
                  <template #body="{ data }">
                    <div class="text-right font-semibold">{{ formatCurrency(data.price) }}</div>
                  </template>
                </Column>
                <Column>
                  <template #body="{ data }">
                    <Field v-if="![2, 3].includes(state.model.order.status)" :name="'order_details[' + data.index + '].quantity'" v-model="data.quantity" v-slot="{ field, meta: metaField, handleChange }">
                      <div class="flex justify-center">
                        <InputNumber
                          class="w-32"
                          inputClass="text-center font-bold"
                          :modelValue="field.value"
                          @update:model-value="handleChange"
                          :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                          showButtons
                          buttonLayout="horizontal"
                          decrementButtonIcon="pi pi-minus"
                          incrementButtonIcon="pi pi-plus"
                          :min="1"
                        />
                      </div>
                    </Field>
                    <div v-else class="text-center font-bold">{{ data.quantity }}</div>
                  </template>
                </Column>
                <Column>
                  <template #body="{ data }">
                    <div class="text-right font-black text-gray-800">{{ formatCurrency(data.sub_total) }}</div>
                  </template>
                </Column>
                <Column v-if="![2, 3].includes(state.model.order.status)">
                  <template #body="{ data }">
                    <div class="flex justify-center">
                      <Button icon="pi pi-trash" class="p-button-rounded p-button-text p-button-danger" @click="removeRow(data.index)" />
                    </div>
                  </template>
                </Column>
              </DataTable>

              <div v-if="![2, 3].includes(state.model.order.status)" class="p-4 bg-gray-50 flex justify-center">
                <Button label="Thêm món / Dịch vụ" icon="pi pi-plus-circle" class="p-button-text p-button-primary font-bold" @click="addRow" />
              </div>
            </div>

            <div class="mt-6">
              <Field name="note" v-model="state.model.order.note" v-slot="{ field, meta: metaField, handleChange }">
                <FloatLabel variant="on">
                  <Textarea
                    rows="3"
                    class="w-full rounded-xl border-gray-200"
                    :modelValue="field.value"
                    @update:model-value="handleChange"
                    :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                    :disabled="[2, 3].includes(state.model.order.status)"
                  />
                  <label>Ghi chú</label>
                </FloatLabel>
              </Field>
            </div>

            <div v-if="[2, 3].includes(state.model.order.status)" class="mt-8 p-6 bg-gray-100 rounded-2xl text-center border-2 border-dashed border-gray-300">
              <i :class="['pi text-3xl mb-2 block', state.model.order.status == 1 ? 'pi-check-circle text-green-500' : 'pi-times-circle text-red-500']"></i>
              <span class="text-xl font-black text-gray-600"> Hóa đơn này {{ state.model.order.status == 1 ? 'ĐÃ THANH TOÁN' : 'ĐÃ HỦY' }} </span>
              <p class="text-gray-500 mt-1">Nội dung đã được khóa và không thể chỉnh sửa thêm.</p>
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>

<style scoped>
:deep(.p-inputnumber-input) {
  /* Resetting the previous style as we now use tailwind classes for width */
  width: 100% !important;
}
</style>
