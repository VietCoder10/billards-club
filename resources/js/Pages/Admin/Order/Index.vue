<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/inertia-vue3';
import moment from 'moment';
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <!-- Form tìm kiếm -->
        <FormSearch :request="$page.props.data.request" :routeName="'admin.order.index'" :createUrl="route('admin.order.create')" :csvRoute="'admin.order.exportCsv'" />

        <!-- Bảng dữ liệu products -->
        <template v-if="$page.props.data.orders.length">
          <div class="flex">
            <div class="group-select-page">
              <LimitPageOption />
            </div>
            <div class="group-paginate">
              <PaginatorCustom :paginator="$page.props.data.paginator" />
            </div>
          </div>

          <div class="p-datatable p-component">
            <div class="p-datatable-table-container">
              <table role="table" class="p-datatable-table">
                <thead class="p-datatable-thead" role="rowgroup" style="position: sticky">
                  <tr>
                    <th class="p-datatable-header-cell w-[132px]">STT</th>
                    <GenerateSort :data="$page.props.data.sortLinks" />
                    <th class="p-datatable-header-cell w-[140px]"></th>
                  </tr>
                </thead>
                <tbody class="p-datatable-tbody">
                  <tr
                    v-for="(order, index) in $page.props.data.orders"
                    :key="order.id"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ index + 1 }}</td>
                    <td>{{ order.table_name }}</td>
                    <td>{{ moment(order.started_at).format('HH:mm - DD/MM/YYYY') }}</td>
                    <td>{{ moment(order.ended_at).format('HH:mm - DD/MM/YYYY') }}</td>
                    <td>{{ order.table_total }}</td>
                    <td>{{ order.service_total }}</td>
                    <td>{{ order.final_total }}</td>
                    <td>{{ order.order_status_label }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.order.show', order.id)"
                        :urlDelete="route('admin.order.destroy', order.id)"
                        messageConfirm="Bạn có chắc chắn muốn xóa đơn hàng này không?"
                        :request="$page.props.data.request"
                        :routeName="'admin.order.index'"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="group-paginate w-full mt-1">
            <PaginatorCustom :paginator="$page.props.data.paginator" />
          </div>
        </template>

        <!-- Hiển thị khi không có dữ liệu -->
        <DataEmpty v-else />
      </Panel>
    </template>
  </AdminLayout>
</template>
