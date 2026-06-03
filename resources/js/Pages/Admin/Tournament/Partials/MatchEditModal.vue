<script setup>
import { reactive, ref, watch, computed } from 'vue';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Button from 'primevue/button';
import { Form as VeeForm, Field, ErrorMessage } from 'vee-validate';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  visible: { type: Boolean, default: false },
  tournamentId: { type: Number, required: true },
  match: { type: Object, default: () => ({}) },
  participants: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:visible', 'success']);

const localVisible = ref(props.visible);
watch(() => props.visible, (v) => (localVisible.value = v));
watch(localVisible, (v) => emit('update:visible', v));

const matchStatusOptions = [
  { label: 'Sắp diễn ra', value: 0 },
  { label: 'Đang đấu', value: 1 },
  { label: 'Đã xong', value: 2 }
];

const state = reactive({
  model: {
    player1_score: 0,
    player2_score: 0,
    winner_id: null,
    status: 0
  }
});

watch(() => props.match, (newMatch) => {
  if (newMatch) {
    state.model.player1_score = newMatch.player1_score || 0;
    state.model.player2_score = newMatch.player2_score || 0;
    state.model.winner_id = newMatch.winner_id;
    state.model.status = newMatch.status || 0;
  }
}, { immediate: true, deep: true });

const matchPlayersOptions = computed(() => {
  const options = [];
  if (props.match?.player1) {
    options.push({
      label: props.match.player1.special_name || props.match.player1.customer?.name || 'Tuyển thủ 1',
      value: props.match.player1.id
    });
  }
  if (props.match?.player2) {
    options.push({
      label: props.match.player2.special_name || props.match.player2.customer?.name || 'Tuyển thủ 2',
      value: props.match.player2.id
    });
  }
  return options;
});

const form = useForm(state.model);

const onSubmit = () => {
  form.player1_score = state.model.player1_score;
  form.player2_score = state.model.player2_score;
  form.winner_id = state.model.winner_id;
  form.status = state.model.status;

  form.put(route('admin.tournament.match.update', { tournament: props.tournamentId, match: props.match.id }), {
    onSuccess: () => {
      localVisible.value = false;
      emit('success');
    }
  });
};

const onInvalidSubmit = ({ errors }) => {
  console.log('Invalid submit', errors);
};
</script>

<template>
  <Dialog v-model:visible="localVisible" header="Cập nhật kết quả" modal :style="{ width: '500px' }">
    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)" id="match-edit-form" class="flex flex-col gap-4 mt-2">
        <div class="grid grid-cols-2 gap-4">
          <div class="field">
            <label class="block mb-1 font-semibold text-center truncate">Điểm: {{ props.match.player1?.special_name || props.match.player1?.customer?.name || 'Tuyển thủ 1' }}</label>
            <InputNumber v-model="state.model.player1_score" showButtons buttonLayout="horizontal" :min="0" class="w-full justify-center" />
          </div>
          <div class="field">
            <label class="block mb-1 font-semibold text-center truncate">Điểm: {{ props.match.player2?.special_name || props.match.player2?.customer?.name || 'Tuyển thủ 2' }}</label>
            <InputNumber v-model="state.model.player2_score" showButtons buttonLayout="horizontal" :min="0" class="w-full justify-center" />
          </div>
        </div>

        <div class="field">
          <label class="block mb-1 font-semibold">Trạng thái trận đấu</label>
          <Select v-model="state.model.status" :options="matchStatusOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>

        <div class="field">
          <label class="block mb-1 font-semibold">Người chiến thắng</label>
          <Select v-model="state.model.winner_id" :options="matchPlayersOptions" optionLabel="label" optionValue="value" placeholder="Chọn người thắng" showClear class="w-full" />
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <Button type="button" label="Hủy" icon="pi pi-times" @click="localVisible = false" class="p-button-text" />
          <Button type="submit" label="Lưu" icon="pi pi-check" :loading="form.processing" autofocus />
        </div>
      </form>
    </VeeForm>
  </Dialog>
</template>
