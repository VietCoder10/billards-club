<template>
  <div class="flex justify-content-center">
    <Button type="button" iconPos="right" icon="pi pi-spin pi-cog" label="Thao tác" @click="toggle" class="btn-action" aria-haspopup="true" aria-controls="overlay_menu" />
    <Menu :submenuLabel="null" ref="menu" id="overlay_menu" class="menu-action w-140px" :model="items" :popup="true">
      <template #item="{ item, props }">
        <Link v-if="!item.deleteFlag" class="flex align-items-center" :href="item.url" v-bind="props.action">
          <span :class="item.icon" />
          <span class="ml-2">{{ item.label }}</span>
        </Link>
        <template v-else>
          <Divider class="action-divider" />
          <a @click.stop.prevent="confirmDelete" class="flex align-items-center" v-bind="props.action">
            <span :class="item.icon" />
            <span class="ml-2">{{ item.label }}</span>
          </a>
        </template>
      </template>
    </Menu>
  </div>
</template>

<script setup>
import Swal from 'sweetalert2';
import { ref } from 'vue';
import { useRequestStore } from '@/store/request';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import { useForm, Link } from '@inertiajs/inertia-vue3';

const toast = useToast();
const props = defineProps(['urlShow', 'urlEdit', 'urlDelete', 'messageConfirm', 'routeName', 'request']);
const menu = ref();

const getItems = () => {
  let menuItems = [];
  if (props.urlShow) {
    menuItems.push({
      label: 'Chi tiết',
      url: props.urlShow,
      icon: 'pi pi-eye'
    });
  }
  if (props.urlEdit) {
    menuItems.push({
      label: 'Chỉnh sửa',
      url: props.urlEdit,
      icon: 'pi pi-file-edit'
    });
  }
  if (props.urlDelete) {
    menuItems.push({
      label: 'Xóa',
      deleteFlag: true,
      url: props.urlDelete,
      icon: 'pi pi-trash'
    });
  }
  return [{ items: menuItems }];
};

const items = ref(getItems());
const confirmDelete = () => {
  Swal.fire({
    title: props.messageConfirm,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: 'var(--p-button-primary-background)',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      useRequestStore().showLoading();
      axios
        .delete(props.urlDelete)
        .then((res) => {
          useRequestStore().hideLoading();
          toast.add({ severity: 'success', summary: res.data.message, life: 3000 });
          setTimeout(() => {
            useForm(props.request ?? {}).get(route(props.routeName));
          }, 1000);
        })
        .catch((error) => {
          useRequestStore().hideLoading();
          toast.add({ severity: 'error', summary: 'An error occurred.', life: 3000 });
        });
    }
  });
};
const toggle = (event) => {
  menu.value.toggle(event);
};
</script>
<style scoped>
.p-menu-submenu-label {
  padding: 0 !important;
}
</style>
