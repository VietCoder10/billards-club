<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/inertia-vue3';
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <!-- Form tìm kiếm -->
        <FormSearch :request="$page.props.data.request" :routeName="'admin.table_price_master.index'" :createUrl="route('admin.table_price_master.create')" :csvRoute="'admin.table_price_master.exportCsv'" />

        <!-- Bảng dữ liệu products -->
        <template v-if="$page.props.data.tablePrices.length">
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
                    v-for="(tablePrice, index) in $page.props.data.tablePrices"
                    :key="tablePrice.id"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ index + 1 }}</td>
                    <td>{{ tablePrice.price_name }}</td>
                    <td>{{ Number(tablePrice.price_per_hour).toLocaleString() }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.table_price_master.edit', tablePrice.id)"
                        :urlDelete="route('admin.table_price_master.destroy', tablePrice.id)"
                        messageConfirm="Bạn có chắc chắn muốn xóa giá bàn này không?"
                        :request="$page.props.data.request"
                        :routeName="'admin.table_price_master.index'"
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
