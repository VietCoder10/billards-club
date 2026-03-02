<script setup>
import { ref, reactive, onMounted, provide, watch, computed } from 'vue';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { useForm, Link } from '@inertiajs/inertia-vue3';
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import TableItem from '@/Components/Billiard/TableItem.vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps(['data', 'tables']);

const tablesDisplay = computed(() => {
  return (
    props.tables || [
      { id: 1, name: 'Bàn 01', status: 'playing', playing_time: '01:20' },
      { id: 2, name: 'Bàn 02', status: 'empty' },
      { id: 3, name: 'Bàn 03', status: 'playing', playing_time: '00:45' },
      { id: 4, name: 'Bàn 04', status: 'empty' },
      { id: 5, name: 'Bàn 05', status: 'empty' },
      { id: 6, name: 'Bàn 06', status: 'playing', playing_time: '02:10' },
      { id: 7, name: 'Bàn 07', status: 'empty' },
      { id: 8, name: 'Bàn 08', status: 'empty' },
      { id: 9, name: 'Bàn 09', status: 'empty' },
      { id: 10, name: 'Bàn 10', status: 'empty' }
    ]
  );
});

const handleTableClick = (table) => {
  // Navigate to order item details
  Inertia.get(route('admin.order-item.index', { id: table.id }));
};

const tabs = reactive([
  { title: 'Tất cả bàn', value: 'all', show: true },
  { title: 'Bàn Trống', value: 'table_no', show: true },
  { title: 'Bàn Đang Chơi', value: 'table_playing', show: true }
]);

const activeTab = ref('all');

const filteredTables = computed(() => {
  if (activeTab.value === 'table_no') {
    return tablesDisplay.value.filter((t) => t.status === 'empty');
  }
  if (activeTab.value === 'table_playing') {
    return tablesDisplay.value.filter((t) => t.status === 'playing');
  }
  return tablesDisplay.value;
});
</script>

<template>
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
            <TabList>
              <Tab class="custom-tab" v-for="tab in tabs" :key="tab.value" :value="tab.value">
                <div class="flex items-center gap-2 px-4 py-2">
                  <span v-if="tab.value === 'all'"><i class="pi pi-th-large"></i></span>
                  <span v-if="tab.value === 'table_no'"><i class="pi pi-circle text-green-500"></i></span>
                  <span v-if="tab.value === 'table_playing'"><i class="pi pi-circle-fill text-red-500"></i></span>
                  {{ tab.title }}
                </div>
              </Tab>
            </TabList>
          </Tabs>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 p-4 bg-gray-50 rounded-xl">
          <TableItem v-for="table in filteredTables" :key="table.id" :table="table" @click="handleTableClick" />
        </div>
        <div v-if="filteredTables.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400">
          <i class="pi pi-inbox text-6xl mb-4"></i>
          <p class="text-xl">Không có bàn nào trong danh mục này</p>
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