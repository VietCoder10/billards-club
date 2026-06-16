<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';
import { ref } from 'vue';

const showPopup = ref(false);
</script>
<template>
  <UserLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <!-- SEARCH -->
        <FormSearch :request="$page.props.data.request" :flagSearchDetail="true" :routeName="'user.invoice.index'" @toggle-filter="showPopup = !showPopup" />
        <!-- TABLE -->
        <template v-if="$page.props.data.invoices.length">
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
                <thead class="p-datatable-thead" role="rowgroup" data-pc-section="thead" style="position: sticky">
                  <tr>
                    <GenerateSort :data="$page.props.data.sortLinks" />
                    <th class="p-datatable-header-cell w-[140px]"></th>
                  </tr>
                </thead>

                <!-- BODY -->
                <tbody class="p-datatable-tbody">
                  <tr
                    v-for="(invoice, index) in $page.props.data.invoices"
                    :key="invoice.id"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ invoice.invoice_number }}</td>
                    <td>{{ invoice.created_user_name }}</td>
                    <td>{{ Number(invoice.table_total).toLocaleString() }}</td>
                    <td>{{ Number(invoice.service_total).toLocaleString() }}</td>
                    <td>{{ Number(invoice.total_amount).toLocaleString() }}</td>
                    <td>{{ Number(invoice.final_amount).toLocaleString() }}</td>
                    <td>{{ invoice.payment_method_label }}</td>
                    <td>
                      <BtnAction v-if="!invoice.deleted_at" :urlEdit="route('user.invoice.show', invoice.id)" :request="$page.props.data.request" :routeName="'user.invoice.index'"></BtnAction>
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

        <DataEmpty v-else />
      </Panel>
    </template>
  </UserLayout>
</template>
