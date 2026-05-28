<script setup>
import { ref, computed, onMounted, reactive, onBeforeUnmount, nextTick } from 'vue';
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { Form as VeeForm, Field, ErrorMessage } from 'vee-validate';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import moment from 'moment';
import { useToast } from 'primevue/usetoast';
import { watch } from 'vue';
import { calcPrice, calcSummary } from '@/lib/common';
import { useDirtyForm } from '@/Composables/useDirtyForm';
import InvoicePopup from './Dialog/InvoicePopup.vue';
const showPopup = ref(false);
const props = defineProps(['data']);
const toast = useToast();
const isSubmitting = ref(false);
const initialState = ref('');

const state = reactive({
  model: {
    order: {},
    order_details: []
  }
});

const categories = ref(props.data.categories || []);
const activeCategory = ref('all');
const searchQuery = ref('');

const filteredProducts = computed(() => {
  let list = props.data.products.data || props.data.products || [];

  if (activeCategory.value !== 'all') {
    list = list.filter((p) => p.category == activeCategory.value);
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter((p) => (p.product_name || '').toLowerCase().includes(q) || (p.category_label || '').toLowerCase().includes(q));
  }

  return list;
});

const normalizeState = (obj) => {
  return JSON.stringify(obj, (_, value) => {
    if (value === null || value === undefined || value === '') return undefined;
    if (value instanceof Date) return value.toISOString();
    return value;
  });
};
const hasUnsavedChanges = computed(() => {
  return normalizeState(state.model) !== initialState.value;
});
useDirtyForm(hasUnsavedChanges, isSubmitting);

const initData = () => {
  const products = props.data.products.data || props.data.products;
  state.model.order = {
    ...props.data.order,
    price_per_hour: props.data.order.price_per_hour ? Number(props.data.order.price_per_hour) : 0,
    total_minutes: props.data.order.total_minutes ? Number(props.data.order.total_minutes) : 0,
    table_total: props.data.order.table_total ? Number(props.data.order.table_total) : 0,
    service_total: props.data.order.service_total ? Number(props.data.order.service_total) : 0,
    final_total: props.data.order.final_total ? Number(props.data.order.final_total) : 0
  };
  state.model.order_details = (props.data.order.details || []).map((item) => {
    const product = products.find((p) => p.id === item.product_id);
    return {
      ...item,
      price: item.price !== null ? Number(item.price) : 0,
      quantity: item.quantity !== null ? Number(item.quantity) : 0,
      sub_total: item.sub_total !== null ? Number(item.sub_total) : 0,
      image: product.avatar_url,
      index: 'key_' + (Math.random() * 100000000000000000).toFixed(0)
    };
  });
  calculatePlayTime();
  nextTick(() => {
    initialState.value = normalizeState(state.model);
  });
};

const calculatePlayTime = () => {
  if (!state.model.order.started_at) return;
  const start = moment(state.model.order.started_at);
  const end = state.model.order.status === 1 ? moment() : moment(state.model.order.ended_at || undefined);
  state.model.order.total_minutes = Math.max(0, end.diff(start, 'minutes'));
  updateTableTotal();
};

const updateTableTotal = () => {
  const minutes = Number(state.model.order.total_minutes || 0);
  const pricePerHour = Number(state.model.order.price_per_hour || 0);
  state.model.order.table_total = (minutes / 60) * pricePerHour;
};

let timerInterval = null;

onMounted(() => {
  initData();
  timerInterval = setInterval(calculatePlayTime, 1000 * 30); // Update every 30s
});

onBeforeUnmount(() => {
  if (timerInterval) clearInterval(timerInterval);
});

const addToOrder = (product) => {
  const found = state.model.order_details.find((i) => i.product_id === product.id);
  if (found) {
    found.quantity++;
    const priceRes = calcPrice(found.price, found.quantity, found.sub_total);
    found.sub_total = priceRes.sub_total;
  } else {
    state.model.order_details.push({
      product_id: product.id,
      product_name: product.product_name,
      price: Number(product.sale_price) || 0,
      quantity: 1,
      sub_total: Number(product.sale_price) || 0,
      image: product.avatar_url,
      index: 'key_' + (Math.random() * 100000000000000000).toFixed(0)
    });
  }
};

const summaryTotals = () => {
  updateTableTotal();
  const summary = calcSummary(state.model.order_details, state.model.order.table_total);
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

const increaseQty = (item) => {
  item.quantity++;
  const priceRes = calcPrice(item.price, item.quantity, item.sub_total);
  item.sub_total = priceRes.sub_total;
};

const decreaseQty = (item) => {
  if (item.quantity > 1) {
    item.quantity--;
    const priceRes = calcPrice(item.price, item.quantity, item.sub_total);
    item.sub_total = priceRes.sub_total;
  } else {
    state.model.order_details = state.model.order_details.filter((i) => i.index !== item.index);
  }
};

const onSubmit = () => {
  isSubmitting.value = true;
  const submitData = {
    ...state.model.order,
    order_details: state.model.order_details,
    service_total: state.model.order.service_total,
    final_total: state.model.order.final_total
  };
  submitData['_method'] = 'PUT';
  useForm(submitData).post(route('admin.order.updateSession', state.model.order.id), {
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

const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
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
          <Button label="Lưu" type="submit" form="order-form" icon="pi pi-save" class="btn-action ml-2"></Button>
        </template>
        <InvoicePopup v-model:visible="showPopup" :request="state.model" v-model:isSubmitting="isSubmitting"></InvoicePopup>
        <VeeForm as="div" v-slot="{ handleSubmit }">
          <form @submit="handleSubmit($event, onSubmit)" id="order-form" class="form-data">
            <div class="grid grid-cols-12 gap-6">
              <!-- Left: Order Summary -->
              <div class="col-span-12 lg:col-span-5 flex flex-col gap-4">
                <div class="card p-4 shadow-sm border rounded-xl bg-white">
                  <div class="flex justify-between items-center mb-4 pb-2 border-bottom">
                    <h3 class="text-lg font-bold text-gray-700">Chi tiết hóa đơn</h3>
                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-medium">{{ state.model.order.table?.table_name }}</span>
                  </div>

                  <div class="flex flex-col gap-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <!-- Time Play -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <i class="pi pi-clock text-blue-600"></i>
                        </div>
                        <div>
                          <p class="font-semibold text-gray-800">Tiền giờ ({{ state.model.order.total_minutes }} phút)</p>
                          <p class="text-xs text-gray-500">{{ formatPrice(state.model.order.price_per_hour) }}/giờ</p>
                        </div>
                      </div>
                      <span class="font-bold text-blue-600">{{ formatPrice(state.model.order.table_total) }}</span>
                    </div>

                    <!-- Order Items -->
                    <div v-for="item in state.model.order_details" :key="item.index" class="flex items-center justify-between p-3 bg-white border rounded-lg hover:border-blue-200 transition-colors">
                      <div class="flex items-center gap-3">
                        <img :src="item.image || '/images/default-avatar.svg'" class="w-12 h-12 rounded-md object-cover" />
                        <div>
                          <p class="font-semibold text-gray-800">{{ item.product_name }}</p>
                          <p class="text-xs text-gray-500">{{ formatPrice(item.price) }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-3">
                        <div class="flex items-center border rounded-lg overflow-hidden">
                          <button type="button" @click="decreaseQty(item)" class="px-2 py-1 bg-gray-50 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <i class="pi pi-minus text-xs"></i>
                          </button>

                          <div class="flex flex-col">
                            <span class="px-3 py-1 font-bold min-w-[30px] text-center">{{ item.quantity }}</span>
                          </div>

                          <button type="button" @click="increaseQty(item)" class="px-2 py-1 bg-gray-50 hover:bg-blue-50 hover:text-blue-500 transition-colors">
                            <i class="pi pi-plus text-xs"></i>
                          </button>
                        </div>
                        <span class="font-bold text-gray-700 w-24 text-right">{{ formatPrice(item.price * item.quantity) }}</span>
                      </div>
                    </div>

                    <div v-if="state.model.order_details.length === 0" class="flex flex-col items-center justify-center py-10 text-gray-400 opacity-60">
                      <i class="pi pi-shopping-bag text-4xl mb-2"></i>
                      <p>Chưa có món nào được thêm</p>
                    </div>
                  </div>

                  <div class="mt-6 pt-4 border-t-2 border-dashed border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-gray-600">Tổng tiền dịch vụ</span>
                      <span class="font-semibold">{{ formatPrice(state.model.order.service_total) }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                      <span class="text-gray-600">Tổng tiền giờ</span>
                      <span class="font-semibold">{{ formatPrice(state.model.order.table_total) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-600 rounded-xl text-white shadow-lg">
                      <span class="text-lg font-medium">Tổng thanh toán</span>
                      <span class="text-2xl font-bold">{{ formatPrice(state.model.order.final_total) }}</span>
                    </div>
                  </div>

                  <div class="flex justify-between items-center gap-2 mt-4">
                    <Button label="Tạm tính" type="submit" form="order-form" icon="pi pi-print" class="p-button-outlined p-button-secondary w-full" />
                    <Button label="Thanh toán" icon="pi pi-check-circle" class="p-button-success w-full" @click="showPopup = !showPopup" />
                  </div>
                </div>
              </div>

              <!-- Right: Product Catalog -->
              <div class="col-span-12 lg:col-span-7 flex flex-col gap-4">
                <div class="card p-4 shadow-sm border rounded-xl bg-white shadow-lg overflow-hidden">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <!-- Categories Dock -->
                    <div class="flex items-center gap-2.5 p-1.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150/80 dark:border-zinc-800 rounded-2xl overflow-x-auto scrollbar-hide select-none max-w-max">
                      <button
                        type="button"
                        @click="activeCategory = 'all'"
                        :class="[
                          'px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2.5 transition-all duration-300 whitespace-nowrap',
                          activeCategory === 'all'
                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20 scale-[1.03] transform'
                            : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-350 hover:bg-zinc-100 dark:hover:bg-zinc-750 border border-zinc-150/60 dark:border-zinc-700/60 hover:border-zinc-300'
                        ]"
                      >
                        <i :class="['pi pi-th-large', activeCategory === 'all' ? 'text-white' : 'text-zinc-400']"></i>
                        Tất cả
                      </button>
                      <button
                        type="button"
                        v-for="cat in categories"
                        :key="cat.value"
                        @click="activeCategory = cat.value"
                        :class="[
                          'px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2.5 transition-all duration-300 whitespace-nowrap',
                          activeCategory === cat.value
                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20 scale-[1.03] transform'
                            : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-350 hover:bg-zinc-100 dark:hover:bg-zinc-750 border border-zinc-150/60 dark:border-zinc-700/60 hover:border-zinc-300'
                        ]"
                      >
                        <i :class="['pi', cat.value == 1 ? 'pi-shopping-bag text-orange-500' : cat.value == 2 ? 'pi-shopping-cart text-cyan-500' : 'pi-tag text-purple-500', activeCategory === cat.value ? '!text-white' : '']"></i>
                        {{ cat.label }}
                      </button>
                    </div>

                    <!-- Search Input Box -->
                    <div class="relative w-full sm:w-64 md:w-80">
                      <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-zinc-400">
                        <i class="pi pi-search text-xs"></i>
                      </span>
                      <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Tìm món ăn, nước uống..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-zinc-150/80 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all duration-300 shadow-sm"
                      />
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-4 overflow-y-auto max-h-[700px] p-1 custom-scrollbar">
                    <div
                      v-if="filteredProducts.length === 0"
                      class="flex flex-col items-center justify-center py-16 w-full text-zinc-400 dark:text-zinc-500 bg-zinc-50/50 dark:bg-zinc-900/30 rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800"
                    >
                      <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-3">
                        <i class="pi pi-search text-lg text-zinc-400"></i>
                      </div>
                      <p class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Không tìm thấy sản phẩm phù hợp</p>
                      <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Vui lòng thử tìm bằng từ khóa khác</p>
                    </div>

                    <div
                      v-else
                      v-for="product in filteredProducts"
                      :key="product.id"
                      @click="addToOrder(product)"
                      class="flex flex-col grow basis-0 gap-2 min-w-[150px] max-w-[200px] p-3 border rounded-xl hover:shadow-xl hover:border-blue-400 cursor-pointer bg-white transition-all group relative overflow-hidden active:scale-95"
                    >
                      <div class="relative overflow-hidden rounded-lg aspect-square mb-2 bg-gray-50">
                        <img :src="product.avatar_url || '/images/default-avatar.svg'" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all flex items-center justify-center">
                          <i class="pi pi-plus text-white scale-0 group-hover:scale-150 transition-all opacity-0 group-hover:opacity-100 drop-shadow-md"></i>
                        </div>
                      </div>
                      <div>
                        <h4 class="font-bold text-gray-800 line-clamp-1 mb-1">{{ product.product_name }}</h4>
                        <p class="text-blue-600 font-bold">{{ formatPrice(product.sale_price) }}</p>
                      </div>
                      <div class="mt-2">
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-gray-100 text-gray-500">
                          {{ product.category_label }}
                        </span>
                      </div>
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

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.border-bottom {
  border-bottom: 1px solid #f1f5f9;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.col-span-12 {
  animation: fadeIn 0.5s ease-out forwards;
}

:deep(.p-button.p-button-success) {
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
  border: none !important;
  box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.2) !important;
}

:deep(.p-button.p-button-success:hover) {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(34, 197, 94, 0.3) !important;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
