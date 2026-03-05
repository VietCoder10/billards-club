<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <!-- Form tìm kiếm -->
        <FormSearch :request="$page.props.data.request" :routeName="'admin.supplier.index'" :createUrl="route('admin.supplier.create')" :csvRoute="'admin.supplier.exportCsv'" />

        <!-- Bảng dữ liệu suppliers -->
        <template v-if="$page.props.data.suppliers.length">
          <div class="flex mb-2">
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
                    <th class="p-datatable-header-cell w-140px">STT</th>
                    <GenerateSort :data="$page.props.data.sortLinks" />
                    <th class="p-datatable-header-cell w-140px">Thao tác</th>
                  </tr>
                </thead>
                <tbody class="p-datatable-tbody">
                  <tr
                    v-for="(supplier, index) in $page.props.data.suppliers"
                    :key="supplier.id"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ index + 1 }}</td>
                    <td>{{ supplier.supplier_name }}</td>
                    <td>{{ supplier.contact_person }}</td>
                    <td>{{ supplier.email }}</td>
                    <td>{{ supplier.phone }}</td>
                    <td>{{ supplier.address }}</td>
                    <td>{{ supplier.note }}</td>
                    <td>{{ supplier.status == 1 ? 'Active' : 'Inactive' }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.supplier.edit', supplier.id)"
                        :urlDelete="route('admin.supplier.destroy', supplier.id)"
                        messageConfirm="このサプライヤーを削除しますか？"
                        :request="$page.props.data.request"
                        :routeName="'admin.supplier.index'"
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
