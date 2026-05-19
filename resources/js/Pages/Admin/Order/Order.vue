<script setup>
import { ref, reactive, onMounted, provide, watch, computed } from 'vue';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import TableItem from '@/Components/Billiard/TableItem.vue';
import { Inertia } from '@inertiajs/inertia';
import { useToast } from 'primevue/usetoast';
import Swal from 'sweetalert2';

const props = defineProps(['data']);
const toast = useToast();

const handleTableClick = (table) => {
  if (table.status === 3) {
    toast.add({
      severity: 'warn',
      summary: 'Thông báo',
      detail: 'Bàn đang bảo trì, không thể mở!',
      life: 3000
    });
    return;
  }

  if (table.status === 1) {
    Swal.fire({
      title: `Bạn muốn bắt đầu mở ${table.table_name}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Mở bàn',
      cancelButtonText: 'Hủy',
      confirmButtonColor: 'var(--p-button-primary-background)',
      cancelButtonColor: '#d33'
    }).then((result) => {
      if (result.isConfirmed) {
        useForm({
          table_id: table.id
        }).post(route('admin.order.store'));
      }
    });
  } else if (table.status === 2 && table.order_id) {
    Inertia.get(route('admin.order-item.edit', table.order_id));
  }
};

const tabs = reactive([
  { title: 'Tất cả bàn', value: 'all', show: true },
  { title: 'Bàn Trống', value: 'table_no', show: true },
  { title: 'Bàn Đang Chơi', value: 'table_playing', show: true },
  { title: 'Bàn Đang Bảo Trì', value: 'table_cleaning', show: true }
]);

const activeTab = ref('all');

const filteredTables = computed(() => {
  if (activeTab.value === 'table_no') {
    return props.data.tables.filter((t) => t.status === 1);
  }
  if (activeTab.value === 'table_playing') {
    return props.data.tables.filter((t) => t.status === 2);
  }
  if (activeTab.value === 'table_cleaning') {
    return props.data.tables.filter((t) => t.status === 3);
  }
  return props.data.tables;
});
</script>

<template>
  <Toast />
  <AdminLayout>
    <template #content>
      <Panel class="header-form" :header="$page.props.data.title">
        <template #header>
          <div class="flex items-center">
            <span class="font-bold text-xl">{{ $page.props.data.title }}</span>
          </div>
        </template>
        <div class="mb-6">
          <Tabs v-model:value="activeTab">
            <TabList class="border-b border-zinc-150 dark:border-zinc-800">
              <Tab class="custom-tab" v-for="tab in tabs" :key="tab.value" :value="tab.value">
                <div class="flex items-center gap-2 px-4 py-2.5 font-bold text-xs transition-colors">
                  <span v-if="tab.value === 'all'"><i class="pi pi-th-large text-zinc-550"></i></span>
                  <span v-if="tab.value === 'table_no'"><i class="pi pi-circle-fill text-rose-500"></i></span>
                  <span v-if="tab.value === 'table_playing'"><i class="pi pi-circle-fill text-emerald-500"></i></span>
                  <span v-if="tab.value === 'table_cleaning'"><i class="pi pi-wrench text-amber-500"></i></span>
                  {{ tab.title }}
                </div>
              </Tab>
            </TabList>
          </Tabs>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 p-5 bg-zinc-50/50 dark:bg-zinc-900/50 border border-zinc-100 dark:border-zinc-850 rounded-2xl">
          <TableItem v-for="table in filteredTables" :key="table.id" :table="table" @click="handleTableClick" />
        </div>
        <div v-if="filteredTables.length === 0" class="flex flex-col items-center justify-center py-20 text-zinc-400">
          <i class="pi pi-inbox text-5xl mb-3 text-zinc-300"></i>
          <p class="text-sm font-semibold">Không có bàn nào trong danh mục này</p>
        </div>
      </Panel>
    </template>
  </AdminLayout>
</template>

<style scoped>
:deep(.custom-tab) {
  padding: 0 !important;
}

:deep(.p-tab) {
  border-bottom: 2px solid transparent;
  transition: all 0.3s;
}

:deep(.p-tab-active) {
  color: #3b82f6 !important;
  border-bottom-color: #3b82f6 !important;
}
</style>