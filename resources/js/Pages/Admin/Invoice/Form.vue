<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, reactive, onMounted, provide, watch, computed, nextTick } from 'vue';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import axios from 'axios';
import { digits, integer, max_value, min_value, required } from '@vee-validate/rules';
import moment from 'moment';
const props = defineProps(['data']);
const state = reactive({
  model: {
    invoice: {},
    invoiceDetails: []
  }
});
onMounted(() => {
  state.model = {
    ...props.data
  };
});
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
            <Button :label="props.data.isEdit ? '戻る' : 'Quay lại'" icon="pi pi-arrow-left" class="btn-action"></Button>
          </Link>
        </template>
        <div class="card flex flex-col gap-4">
          <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 border border-blue-200 grow basis-0 min-w-[200px]">
              <i class="pi pi-briefcase text-blue-500 text-2xl"></i>
              <div class="flex flex-col">
                <span class="text-sm text-gray-500">Tiền dịch vụ</span>
                <span class="text-xl font-bold text-blue-600">{{ Number(state.model.invoice.service_total).toLocaleString() }}đ </span>
              </div>
            </div>

            <!-- Tiền bàn -->
            <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 border border-green-200 grow basis-0 min-w-[200px]">
              <i class="pi pi-table text-green-500 text-2xl"></i>
              <div class="flex flex-col">
                <span class="text-sm text-gray-500">Tiền bàn</span>
                <span class="text-xl font-bold text-green-600">{{ Number(state.model.invoice.table_total).toLocaleString() }} đ </span>
              </div>
            </div>
          </div>
          <Divider />
          <div class="flex flex-wrap gap-4">
            <div class="flex flex-col grow basis-0 gap-3">
              <DataTable class="w-full tbl-form" :value="state.model.invoiceDetails" showGridlines>
                <ColumnGroup type="header">
                  <Row>
                    <Column header="Tên sản phẩm" />
                    <Column header="Số lượng" />
                    <Column header="Giá tiền" />
                    <Column header="Tổng tiền" />
                  </Row>
                </ColumnGroup>
                <Column class="text-center">
                  <template #body="{ data }">
                    {{ data.item_name }}
                  </template>
                </Column>
                <Column class="text-center">
                  <template #body="{ data }">
                    {{ data.quantity }}
                  </template>
                </Column>
                <Column class="text-center">
                  <template #body="{ data }"> {{ data.price }} </template>
                </Column>
                <Column class="text-center">
                  <template #body="{ data }"> {{ data.sub_total }}</template>
                </Column>
              </DataTable>
            </div>
          </div>
        </div>
      </Panel>
    </template>
  </AdminLayout>
</template>