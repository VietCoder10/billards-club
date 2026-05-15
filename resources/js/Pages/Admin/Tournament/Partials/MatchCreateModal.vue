<script setup>
import { reactive, ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import { Form as VeeForm, Field, ErrorMessage } from 'vee-validate';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  visible: { type: Boolean, default: false },
  tournamentId: { type: Number, required: true },
  participants: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:visible', 'success']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => (localVisible.value = v));
watch(localVisible, (v) => emit('update:visible', v));

const state = reactive({
  model: {
    round_name: '',
    player1_id: null,
    player2_id: null,
  }
});

const form = useForm(state.model);

const onSubmit = () => {
  form.round_name = state.model.round_name;
  form.player1_id = state.model.player1_id;
  form.player2_id = state.model.player2_id;
  
  form.post(route('admin.tournament.match.store', props.tournamentId), {
    onSuccess: () => {
      localVisible.value = false;
      state.model.round_name = '';
      state.model.player1_id = null;
      state.model.player2_id = null;
      emit('success');
    }
  });
};

const onInvalidSubmit = ({ errors }) => {
  console.log('Invalid submit', errors);
};
</script>

<template>
  <Dialog v-model:visible="localVisible" header="Tạo trận đấu mới" modal :style="{ width: '500px' }">
    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)" id="match-create-form" class="flex flex-col gap-4 mt-2">
        <div class="field">
          <label class="block mb-1 font-semibold">Tên vòng đấu</label>
          <Field name="round_name" rules="required" v-model="state.model.round_name">
            <InputText v-model="state.model.round_name" class="w-full" placeholder="Vòng 1/16, Tứ kết..." />
          </Field>
          <ErrorMessage name="round_name" class="text-red-500 text-xs mt-1" />
        </div>

        <div class="field">
          <label class="block mb-1 font-semibold">Tuyển thủ 1</label>
          <Field name="player1_id" rules="required" v-model="state.model.player1_id">
            <Select v-model="state.model.player1_id" :options="participants" optionLabel="label" optionValue="value" placeholder="Chọn tuyển thủ" class="w-full" />
          </Field>
          <ErrorMessage name="player1_id" class="text-red-500 text-xs mt-1" />
        </div>

        <div class="field">
          <label class="block mb-1 font-semibold">Tuyển thủ 2</label>
          <Field name="player2_id" rules="required" v-model="state.model.player2_id">
            <Select v-model="state.model.player2_id" :options="participants" optionLabel="label" optionValue="value" placeholder="Chọn tuyển thủ" class="w-full" />
          </Field>
          <ErrorMessage name="player2_id" class="text-red-500 text-xs mt-1" />
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <Button type="button" label="Hủy" icon="pi pi-times" @click="localVisible = false" class="p-button-text" />
          <Button type="submit" label="Lưu" icon="pi pi-check" :loading="form.processing" autofocus />
        </div>
      </form>
    </VeeForm>
  </Dialog>
</template>
