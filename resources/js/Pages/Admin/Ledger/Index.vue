<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
// import { ref, onMounted, reactive } from 'vue';
// import axios from 'axios';
// const state = reactive({
//   urlDownload: {}
// });
// const exportCsv = () => {
//   axios
//     .post(route('admin.ledger.exportCsv', props.data.request))
//     .then(function (res) {
//       state.urlDownload = res.data.url;
//       // setTimeout(function () {
//       //   that.$refs.linkDownload.click();
//       //   $(".loading-div").addClass("hidden");
//       // }, 500);
//     })
//     .catch(() => {});
// };
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <FormSearch :request="$page.props.data.request" :routeName="'admin.ledger.index'" :createUrl="route('admin.ledger.create')" :csvRoute="'admin.building.exportCsv'"></FormSearch>
        <template v-if="$page.props.data.buildings.length">
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
                    <th class="p-datatable-header-cell w-140px"></th>
                  </tr>
                </thead>
                <tbody class="p-datatable-tbody">
                  <tr
                    v-for="(building, index) in $page.props.data.buildings"
                    :key="index"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ building.building_code }}</td>
                    <td>{{ building.building_name }}</td>
                    <td>{{ building.building_name_kana }}</td>
                    <td>{{ building.building_short_name }}</td>
                    <td>{{ building.person_in_charge }}</td>
                    <td>{{ building.construction_reason }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.ledger.edit', building.id)"
                        :urlDelete="route('admin.ledger.destroy', building.id)"
                        messageConfirm="このユーザーを削除しますか？"
                        :request="$page.props.data.request"
                        :routeName="'admin.ledger.index'"
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
