<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';

const categories = ref([
  { id: 'all', name: 'Tất cả' },
  { id: 'drink', name: 'Đồ uống' },
  { id: 'food', name: 'Đồ ăn' }
]);

const activeCategory = ref('all');

const products = ref([
  { id: 1, name: 'Bia Tiger', price: 25000, category: 'drink', image: 'https://images.unsplash.com/photo-1618885472179-5e474019f2a9?q=80&w=100&auto=format&fit=crop' },
  { id: 2, name: 'Nước suối', price: 10000, category: 'drink', image: 'https://images.unsplash.com/photo-1560023907-5f339617ea30?q=80&w=100&auto=format&fit=crop' },
  { id: 3, name: 'Redbull', price: 20000, category: 'drink', image: 'https://images.unsplash.com/photo-1622484210802-45e050307042?q=80&w=100&auto=format&fit=crop' },
  { id: 4, name: 'Mì cay', price: 45000, category: 'food', image: 'https://images.unsplash.com/photo-1552611052-33e04de081de?q=80&w=100&auto=format&fit=crop' },
  { id: 5, name: 'Xúc xích', price: 15000, category: 'food', image: 'https://images.unsplash.com/photo-1585325701956-60dd9c8553bc?q=80&w=100&auto=format&fit=crop' }
]);

const filteredProducts = computed(() => {
  if (activeCategory.value === 'all') return products.value;
  return products.value.filter((p) => p.category === activeCategory.value);
});

const orderItems = ref([]);
const addToOrder = (product) => {
  const found = orderItems.value.find((i) => i.id === product.id);
  found ? found.qty++ : orderItems.value.push({ ...product, qty: 1 });
};

// play time (demo data)
const playMinutes = ref(83);
const pricePerHour = 60000;
const playAmount = computed(() => Math.floor((playMinutes.value / 60) * pricePerHour));

const total = computed(() => playAmount.value + orderItems.value.reduce((s, i) => s + i.qty * i.price, 0));
const props = defineProps(['data']);

// realtime timer (UI demo)
const timer = ref('01:23:45');

const increaseQty = (item) => {
  item.qty++;
};

const decreaseQty = (item) => {
  if (item.qty > 1) {
    item.qty--;
  } else {
    orderItems.value = orderItems.value.filter((i) => i.id !== item.id);
  }
};

const onSubmit = (values) => {
  console.log('Form submitted:', values);
  // Handle saving the order
};

const onInvalidSubmit = ({ errors }) => {
  console.log('Validation failed:', errors);
};

const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
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
            <Button label="Back " icon="pi pi-arrow-left" class="btn-action"></Button>
          </Link>
          <Button label="Save" type="submit" form="order-form" icon="pi pi-save" class="btn-action ml-2"></Button>
        </template>
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" id="order-form" class="form-data">
            <div class="grid grid-cols-12 gap-6">
              <!-- Left: Order Summary -->
              <div class="col-span-12 lg:col-span-5 flex flex-col gap-4">
                <div class="card p-4 shadow-sm border rounded-xl bg-white">
                  <div class="flex justify-between items-center mb-4 pb-2 border-bottom">
                    <h3 class="text-lg font-bold text-gray-700">Chi tiết hóa đơn</h3>
                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-medium">Bàn 01</span>
                  </div>

                  <div class="flex flex-col gap-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <!-- Time Play -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <i class="pi pi-clock text-blue-600"></i>
                        </div>
                        <div>
                          <p class="font-semibold text-gray-800">Tiền giờ ({{ playMinutes }} phút)</p>
                          <p class="text-xs text-gray-500">{{ formatPrice(pricePerHour) }}/giờ</p>
                        </div>
                      </div>
                      <span class="font-bold text-blue-600">{{ formatPrice(playAmount) }}</span>
                    </div>

                    <!-- Order Items -->
                    <div v-for="(item, i) in orderItems" :key="item.id" class="flex items-center justify-between p-3 bg-white border rounded-lg hover:border-blue-200 transition-colors">
                      <div class="flex items-center gap-3">
                        <img :src="item.image" class="w-12 h-12 rounded-md object-cover" />
                        <div>
                          <p class="font-semibold text-gray-800">{{ item.name }}</p>
                          <p class="text-xs text-gray-500">{{ formatPrice(item.price) }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-3">
                        <div class="flex items-center border rounded-lg overflow-hidden">
                          <button type="button" @click="decreaseQty(item)" class="px-2 py-1 bg-gray-50 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <i class="pi pi-minus text-xs"></i>
                          </button>

                          <div class="flex flex-col">
                            <Field :name="`orderItems[${i}].qty`" v-model="item.qty" rules="numeric|min_value:1" v-slot="{ field }">
                              <span class="px-3 py-1 font-bold min-w-[30px] text-center" v-bind="field">{{ item.qty }}</span>
                            </Field>
                            <ErrorMessage class="p-error text-[10px]" :name="`orderItems[${i}].qty`" />
                          </div>

                          <button type="button" @click="increaseQty(item)" class="px-2 py-1 bg-gray-50 hover:bg-blue-50 hover:text-blue-500 transition-colors">
                            <i class="pi pi-plus text-xs"></i>
                          </button>
                        </div>
                        <span class="font-bold text-gray-700 w-24 text-right">{{ formatPrice(item.price * item.qty) }}</span>
                      </div>
                    </div>

                    <div v-if="orderItems.length === 0" class="flex flex-col items-center justify-center py-10 text-gray-400 opacity-60">
                      <i class="pi pi-shopping-bag text-4xl mb-2"></i>
                      <p>Chưa có món nào được thêm</p>
                    </div>
                  </div>

                  <div class="mt-6 pt-4 border-t-2 border-dashed border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-gray-600">Tổng tiền dịch vụ</span>
                      <span class="font-semibold">{{ formatPrice(total - playAmount) }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                      <span class="text-gray-600">Tổng tiền giờ</span>
                      <span class="font-semibold">{{ formatPrice(playAmount) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-600 rounded-xl text-white">
                      <span class="text-lg font-medium">Tổng thanh toán</span>
                      <span class="text-2xl font-bold">{{ formatPrice(total) }}</span>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-3 mt-4">
                    <Button label="Tạm tính" icon="pi pi-print" class="p-button-outlined p-button-secondary w-full" />
                    <Button label="Thanh toán" icon="pi pi-check-circle" class="p-button-success w-full" />
                  </div>
                </div>
              </div>

              <!-- Right: Product Catalog -->
              <div class="col-span-12 lg:col-span-7 flex flex-col gap-4">
                <div class="card p-4 shadow-sm border rounded-xl bg-white shadow-lg overflow-hidden">
                  <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                    <button
                      type="button"
                      v-for="cat in categories"
                      :key="cat.id"
                      @click="activeCategory = cat.id"
                      :class="['px-6 py-2 rounded-full font-semibold transition-all whitespace-nowrap', activeCategory === cat.id ? 'bg-blue-600 text-white shadow-md transform scale-105' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                    >
                      <i :class="['pi mr-2', cat.id === 'all' ? 'pi-th-large' : cat.id === 'drink' ? 'pi-palette' : 'pi-apple']"></i>
                      {{ cat.name }}
                    </button>
                  </div>

                  <div class="flex flex-wrap gap-4 overflow-y-auto max-h-[700px] p-1 custom-scrollbar">
                    <div
                      v-for="product in filteredProducts"
                      :key="product.id"
                      @click="addToOrder(product)"
                      class="flex flex-col grow basis-0 gap-2 min-w-[150px] max-w-[200px] p-3 border rounded-xl hover:shadow-xl hover:border-blue-400 cursor-pointer bg-white transition-all group relative overflow-hidden active:scale-95"
                    >
                      <div class="relative overflow-hidden rounded-lg aspect-square mb-2 bg-gray-50">
                        <img :src="product.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all flex items-center justify-center">
                          <i class="pi pi-plus text-white scale-0 group-hover:scale-150 transition-all opacity-0 group-hover:opacity-100 drop-shadow-md"></i>
                        </div>
                      </div>
                      <div>
                        <h4 class="font-bold text-gray-800 line-clamp-1 mb-1">{{ product.name }}</h4>
                        <p class="text-blue-600 font-bold">{{ formatPrice(product.price) }}</p>
                      </div>
                      <div class="mt-2">
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-gray-100 text-gray-500">
                          {{ product.category === 'drink' ? 'Nước uống' : 'Thức ăn' }}
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
