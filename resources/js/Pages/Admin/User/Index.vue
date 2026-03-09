<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, onMounted } from 'vue';
import AvatarUploadModal from '../../../Components/Billiard/AvatarUploadModal.vue';
const page = usePage();

const selectedIds = ref([]);
const showAvatarModal = ref(false);
const selectedStaffId = ref(null);
const selectedStaffAvatar = ref(null);

const openAvatarModal = (user) => {
  selectedStaffId.value = user.id;
  selectedStaffAvatar.value = user.avatar_url || '/images/default-avatar.svg';
  showAvatarModal.value = true;
};

const handleAvatarUploaded = (newAvatar) => {
  // Update the user avatar in the list
  const userIndex = page.props.value.data.users.findIndex((u) => u.id === selectedStaffId.value);
  if (userIndex !== -1) {
    page.props.value.data.users[userIndex].avatar_url = newAvatar;
  }
};
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <FormSearch :request="$page.props.data.request" :routeName="'admin.user.index'" :createUrl="route('admin.user.create')"></FormSearch>
        <template v-if="$page.props.data.users.length">
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
                    v-for="(user, index) in $page.props.data.users"
                    :key="index"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td class="cursor-pointer" @click="openAvatarModal(user)">
                      <img :src="user.avatar_url || '/images/default-avatar.svg'" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-gray-300 hover:border-blue-500 transition" />
                    </td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.role_label }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.user.edit', user.id)"
                        :urlDelete="route('admin.user.destroy', user.id)"
                        messageConfirm="Bạn có muốn xóa người dùng này không"
                        :request="$page.props.data.request"
                        :routeName="'admin.user.index'"
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
      <AvatarUploadModal v-model:visible="showAvatarModal" :userId="selectedStaffId" :route_url="'admin.user.updateAvatar'" :currentAvatar="selectedStaffAvatar" @uploaded="handleAvatarUploaded" />
    </template>
  </AdminLayout>
</template>
