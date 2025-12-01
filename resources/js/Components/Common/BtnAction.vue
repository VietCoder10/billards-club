<template>
  <div class="flex justify-content-center">
    <Button type="button" iconPos="right" icon="pi pi-spin pi-cog" label="操作選択" @click="toggle" class="btn-action" aria-haspopup="true" aria-controls="overlay_menu" />
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
const props = defineProps(['urlEdit', 'urlDelete', 'messageConfirm', 'routeName', 'request']);
const menu = ref();
const items = ref([
  {
    items: [
      {
        label: '編集',
        url: props.urlEdit,
        icon: 'pi pi-file-edit'
      },
      {
        label: '削除',
        deleteFlag: true,
        url: props.urlDelete,
        icon: 'pi pi-trash'
      }
    ]
  }
]);
const confirmDelete = () => {
  Swal.fire({
    title: props.messageConfirm,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: 'var(--p-button-primary-background)',
    cancelButtonColor: '#d33',
    confirmButtonText: '削除',
    cancelButtonText: 'キャンセル'
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
          toast.add({ severity: 'error', summary: 'エラーが発生しました。', life: 3000 });
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
