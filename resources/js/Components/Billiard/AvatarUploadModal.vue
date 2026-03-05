<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
  visible: { type: Boolean, default: false },
  userId: { type: Number, default: null },
  currentAvatar: { type: String, default: null }
});

const emit = defineEmits(['update:visible', 'uploaded']);

const localVisible = ref(props.visible);
const selectedFile = ref(null);
const previewUrl = ref(null);
const uploading = ref(false);
const toast = useToast();

watch(
  () => props.visible,
  (val) => {
    localVisible.value = val;
    if (val && props.currentAvatar) {
      previewUrl.value = props.currentAvatar;
    }
  }
);

watch(localVisible, (val) => {
  emit('update:visible', val);
  if (!val) {
    selectedFile.value = null;
    previewUrl.value = null;
  }
});

const onFileSelect = (event) => {
  const file = event.files[0];
  if (file) {
    selectedFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const uploadAvatar = async () => {
  if (!selectedFile.value) {
    toast.add({ severity: 'warn', summary: 'Cảnh báo', detail: 'Vui lòng chọn hình ảnh.', life: 3000 });
    return;
  }

  uploading.value = true;
  const formData = new FormData();
  formData.append('avatar', selectedFile.value);

  try {
    const response = await axios.post(route('admin.product.updateAvatar', props.userId), formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      toast.add({ severity: 'success', summary: 'Thành công', detail: 'Ảnh đại diện đã được cập nhật.', life: 3000 });
      emit('uploaded', response.data.avatar);
      localVisible.value = false;
    }
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Lỗi', detail: 'Vui lòng chọn hình ảnh định dạng jpeg, png, jpg, gif.', life: 3000 });
  } finally {
    uploading.value = false;
  }
};
</script>

<template>
  <Dialog v-model:visible="localVisible" header="Thay đổi ảnh đại diện" modal :style="{ width: '500px' }" :breakpoints="{ '960px': '75vw', '641px': '90vw' }">
    <div class="flex flex-col gap-4">
      <div v-if="previewUrl" class="flex justify-center">
        <img :src="previewUrl" alt="Avatar Preview" class="w-32 h-32 rounded-full object-cover border-2 border-gray-300" />
      </div>

      <FileUpload mode="basic" accept="image/*" :maxFileSize="2000000" :auto="false" chooseLabel="Chọn ảnh" @select="onFileSelect" :customUpload="true" invalidFileSizeMessage="Kích thước tệp quá lớn. Vui lòng chọn ảnh dưới 2MB." />

      <div class="flex justify-end gap-2">
        <Button label="Hủy" severity="secondary" @click="localVisible = false" />
        <Button label="Tải lên" :loading="uploading" @click="uploadAvatar" />
      </div>
    </div>
  </Dialog>
</template>
