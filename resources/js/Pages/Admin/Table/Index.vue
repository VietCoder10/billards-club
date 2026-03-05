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
        <FormSearch :request="$page.props.data.request" :routeName="'admin.table.index'" :createUrl="route('admin.table.create')" :csvRoute="'admin.table.exportCsv'" />

        <!-- Bảng dữ liệu products -->
        <template v-if="$page.props.data.tables.length">
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
                    v-for="(table, index) in $page.props.data.tables"
                    :key="table.id"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ index + 1 }}</td>
                    <td>{{ table.table_name }}</td>
                    <td>{{ table.status_label }}</td>
                    <td>{{ table.price_per_hour }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.table.edit', table.id)"
                        :urlDelete="route('admin.table.destroy', table.id)"
                        messageConfirm="Bạn có chắc chắn muốn xóa bàn này không?"
                        :request="$page.props.data.request"
                        :routeName="'admin.table.index'"
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
