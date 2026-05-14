<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, onMounted } from 'vue';
import AvatarUploadModal from '../../../Components/Billiard/AvatarUploadModal.vue';
const page = usePage();

const selectedIds = ref([]);
const showAvatarModal = ref(false);
const selectedCustomerId = ref(null);
const selectedCustomerAvatar = ref(null);

const openAvatarModal = (customer) => {
  selectedCustomerId.value = customer.id;
  selectedCustomerAvatar.value = customer.avatar_url || '/images/default-avatar.svg';
  showAvatarModal.value = true;
};

const handleAvatarUploaded = (newAvatar) => {
  // Update the customer avatar in the list
  const customerIndex = page.props.value.data.customers.findIndex((c) => c.id === selectedCustomerId.value);
  if (customerIndex !== -1) {
    page.props.value.data.customers[customerIndex].avatar_url = newAvatar;
  }
};
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <FormSearch :request="$page.props.data.request" :routeName="'admin.customer.index'" :createUrl="route('admin.customer.create')"></FormSearch>
        <template v-if="$page.props.data.customers.length">
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
                    <th class="p-datatable-header-cell w-[120px]">Ảnh đại diện</th>
                    <GenerateSort :data="$page.props.data.sortLinks"></GenerateSort>
                    <th class="p-datatable-header-cell w-[140px]"></th>
                  </tr>
                </thead>
                <tbody class="p-datatable-tbody">
                  <tr
                    v-for="(customer, index) in $page.props.data.customers"
                    :key="index"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td class="cursor-pointer" @click="openAvatarModal(customer)">
                      <img :src="customer.avatar_url || '/images/default-avatar.svg'" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-gray-300 hover:border-blue-500 transition" />
                    </td>
                    <td>{{ customer.name }}</td>
                    <td>{{ customer.email }}</td>
                    <td>{{ customer.phone }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.customer.edit', customer.id)"
                        :urlDelete="route('admin.customer.destroy', customer.id)"
                        messageConfirm="Bạn có muốn xóa khách hàng này không"
                        :request="$page.props.data.request"
                        :routeName="'admin.customer.index'"
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
      <AvatarUploadModal v-model:visible="showAvatarModal" :userId="selectedCustomerId" :route_url="'admin.customer.updateAvatar'" :currentAvatar="selectedCustomerAvatar" @uploaded="handleAvatarUploaded" />
    </template>
  </AdminLayout>
</template>
