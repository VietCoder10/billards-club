<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { usePage } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import moment from 'moment';

const page = usePage();

const getStatusLabel = (status) => {
  const map = {
    0: 'Bản nháp',
    1: 'Mở đăng ký',
    2: 'Đang diễn ra',
    3: 'Đã kết thúc',
    4: 'Đã hủy'
  };
  return map[status] || 'Unknown';
};

const getStatusClass = (status) => {
  const map = {
    0: 'bg-gray-200 text-gray-800',
    1: 'bg-green-100 text-green-800',
    2: 'bg-blue-100 text-blue-800',
    3: 'bg-purple-100 text-purple-800',
    4: 'bg-red-100 text-red-800'
  };
  return map[status] || '';
};

const formatDate = (date) => {
  if (!date) return '';
  return moment(date).format('DD/MM/YYYY HH:mm');
};
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <FormSearch :request="$page.props.data.request" :routeName="'admin.tournament.index'" :createUrl="route('admin.tournament.create')"></FormSearch>
        <template v-if="$page.props.data.tournaments.length">
          <div class="flex">
            <div class="group-select-page">
              <LimitPageOption />
            </div>
            <div class="group-paginate">
              <PaginatorCustom :paginator="$page.props.data.paginator"></PaginatorCustom>
            </div>
          </div>

          <div class="p-datatable p-component">
            <div class="p-datatable-table-container">
              <table role="table" class="p-datatable-table">
                <thead class="p-datatable-thead" role="rowgroup" data-pc-section="thead" style="position: sticky">
                  <tr>
                    <GenerateSort :data="$page.props.data.sortLinks"></GenerateSort>
                    <th class="p-datatable-header-cell">Hạn đăng ký</th>
                    <th class="p-datatable-header-cell">Phí tham gia</th>
                    <th class="p-datatable-header-cell w-[140px]"></th>
                  </tr>
                </thead>
                <tbody class="p-datatable-tbody">
                  <tr
                    v-for="(tournament, index) in $page.props.data.tournaments"
                    :key="index"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ tournament.name }}</td>
                    <td>{{ formatDate(tournament.start_date) }} - {{ formatDate(tournament.end_date) }}</td>
                    <td>
                      <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusClass(tournament.status)]">
                        {{ getStatusLabel(tournament.status) }}
                      </span>
                    </td>
                    <td>{{ formatDate(tournament.registration_deadline) }}</td>
                    <td>{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(tournament.entry_fee) }}</td>
                    <td>
                      <BtnAction
                        :urlShow="route('admin.tournament.show', tournament.id)"
                        :urlEdit="route('admin.tournament.edit', tournament.id)"
                        :urlDelete="route('admin.tournament.destroy', tournament.id)"
                        messageConfirm="Bạn có chắc chắn muốn xóa giải đấu này không?"
                        :request="$page.props.data.request"
                        :routeName="'admin.tournament.index'"
                      ></BtnAction>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="group-paginate w-full mt-1">
            <PaginatorCustom :paginator="$page.props.data.paginator"></PaginatorCustom>
          </div>
        </template>
        <DataEmpty v-else></DataEmpty>
      </Panel>
    </template>
  </AdminLayout>
</template>
