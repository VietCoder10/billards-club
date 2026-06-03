<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/inertia-vue3';
import AvatarUploadModal from '../../../Components/Billiard/AvatarUploadModal.vue';

const page = usePage();
const selectedIds = ref([]);
const showAvatarModal = ref(false);
const selectedProductId = ref(null);
const selectedProductAvatar = ref(null);
const openAvatarModal = (product) => {
  selectedProductId.value = product.id;
  selectedProductAvatar.value = product.avatar_url || '/images/default-avatar.svg';
  showAvatarModal.value = true;
};

const handleAvatarUploaded = (newAvatar) => {
  // Update the product avatar in the list
  const productIndex = page.props.value.data.products.findIndex((p) => p.id === selectedProductId.value);
  if (productIndex !== -1) {
    page.props.value.data.products[productIndex].avatar_url = newAvatar;
  }
};
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <!-- Form tìm kiếm -->
        <FormSearch :request="$page.props.data.request" :routeName="'admin.product.index'" :createUrl="route('admin.product.create')" />

        <!-- Bảng dữ liệu products -->
        <template v-if="$page.props.data.products.length">
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
                    <th class="p-datatable-header-cell w-140px">Ảnh</th>
                    <GenerateSort :data="$page.props.data.sortLinks" />
                    <th class="p-datatable-header-cell w-140px"></th>
                  </tr>
                </thead>
                <tbody class="p-datatable-tbody">
                  <tr
                    v-for="(product, index) in $page.props.data.products"
                    :key="product.id"
                    :class="{
                      'p-row-odd': index % 2 === 0,
                      'p-row-even': index % 2 !== 0
                    }"
                  >
                    <td>{{ index + 1 }}</td>
                    <td class="cursor-pointer" @click="openAvatarModal(product)">
                      <img :src="product.avatar_url || '/images/default-avatar.svg'" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-gray-300 hover:border-blue-500 transition" />
                    </td>
                    <td>{{ product.product_name }}</td>
                    <td>{{ product.category }}</td>
                    <td>{{ product.sku }}</td>
                    <td>{{ product.supplier_name }}</td>
                    <td>{{ product.cost_price }}</td>
                    <td>{{ product.quantity }}</td>
                    <td>
                      <BtnAction
                        :urlEdit="route('admin.product.edit', product.id)"
                        :urlDelete="route('admin.product.destroy', product.id)"
                        messageConfirm="Bạn có chắc chắn muốn xóa sản phẩm này không?"
                        :request="$page.props.data.request"
                        :routeName="'admin.product.index'"
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
      <AvatarUploadModal v-model:visible="showAvatarModal" :route_url="'admin.product.updateAvatar'" :userId="selectedProductId" :currentAvatar="selectedProductAvatar" @uploaded="handleAvatarUploaded" />
    </template>
  </AdminLayout>
</template>
