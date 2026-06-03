<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Button from 'primevue/button';
import { Form as VeeForm, Field, ErrorMessage, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  visible: { type: Boolean, default: false },
  tournamentId: { type: Number, required: true },
  match: { type: Object, default: () => ({}) },
  participants: { type: Array, default: () => [] },
  matchStatusOptions: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:visible', 'success']);

const localVisible = ref(props.visible);
watch(
  () => props.visible,
  (v) => (localVisible.value = v)
);
watch(localVisible, (v) => emit('update:visible', v));


const state = reactive({
  model: {
    player1_score: 0,
    player2_score: 0,
    winner_id: null,
    status: 1
  }
});

watch(
  () => props.match,
  (newMatch) => {
    if (newMatch) {
      state.model.player1_score = newMatch.player1_score || 0;
      state.model.player2_score = newMatch.player2_score || 0;
      state.model.winner_id = newMatch.winner_id;
      state.model.status = newMatch.status || 1;
    }
  },
  { immediate: true, deep: true }
);

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

onMounted(() => {
  setMessageError();
});

const setMessageError = () => {
  let messError = {
    en: {
      fields: {
        player1_score: {
          required: 'Vui lòng nhập điểm tuyển thủ 1',
          min_value: 'Điểm tuyển thủ 1 không được nhỏ hơn 0'
        },
        player2_score: {
          required: 'Vui lòng nhập điểm tuyển thủ 2',
          min_value: 'Điểm tuyển thủ 2 không được nhỏ hơn 0'
        },
        status: {
          required: 'Vui lòng chọn trạng thái trận đấu'
        },
        winner_id: {
          required: 'Vui lòng chọn người chiến thắng'
        }
      }
    }
  };
  configure({
    generateMessage: localize(messError)
  });
};

const onSubmit = (values, { setErrors }) => {
  form.player1_score = state.model.player1_score;
  form.player2_score = state.model.player2_score;
  form.winner_id = state.model.winner_id;
  form.status = state.model.status;

  form.put(route('admin.tournament.match.update', { tournament: props.tournamentId, match: props.match.id }), {
    onSuccess: () => {
      localVisible.value = false;
      emit('success');
    },
    onError: (errors) => {
      setErrors(errors);
      onInvalidSubmit({ errors });
    }
  });
};

const onInvalidSubmit = ({ errors }) => {
  console.log('Invalid submit', errors);
};
</script>

<template>
  <Dialog v-model:visible="localVisible" header="Cập nhật kết quả" modal :style="{ width: '40rem' }">
    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)" id="match-edit-form" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-4">
          <div class="flex flex-wrap gap-4">
            <div class="flex flex-col gap-2">
              <Field name="player1_score" rules="required|min_value:0" v-model="state.model.player1_score" v-slot="{ field, meta: metaField, handleChange }">
                <label>Điểm: {{ props.match.player1?.special_name || props.match.player1?.customer?.name || 'Tuyển thủ 1' }}</label>
                <InputNumber inputClass="max-w-[100px]" :modelValue="field.value" @update:modelValue="handleChange" showButtons buttonLayout="horizontal" :min="0" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                <ErrorMessage class="p-error" name="player1_score" />
              </Field>
            </div>
            <div class="flex flex-col gap-2">
              <Field name="player2_score" rules="required|min_value:0" v-model="state.model.player2_score" v-slot="{ field, meta: metaField, handleChange }">
                <label>Điểm: {{ props.match.player2?.special_name || props.match.player2?.customer?.name || 'Tuyển thủ 2' }}</label>
                <InputNumber inputClass="max-w-[100px]" :modelValue="field.value" @update:modelValue="handleChange" showButtons buttonLayout="horizontal" :min="0" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                <ErrorMessage class="p-error" name="player2_score" />
              </Field>
            </div>
          </div>
          <div class="flex flex-wrap gap-4">
            <div class="flex flex-col grow basis-0 gap-2 min-w-[150px]">
              <Field name="status" rules="required" v-model="state.model.status" v-slot="{ field, meta: metaField, handleChange }">
                <label class="block mb-1 font-semibold">Trạng thái trận đấu</label>
                <Select :modelValue="field.value" @update:modelValue="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" :options="matchStatusOptions" optionLabel="label" optionValue="value" class="w-full" />
                <ErrorMessage class="p-error" name="status" />
              </Field>
            </div>
          </div>
          <div class="flex flex-wrap gap-4">
            <div class="flex flex-col grow basis-0 gap-2 min-w-[150px]">
              <Field name="winner_id" v-model="state.model.winner_id" v-slot="{ field, meta: metaField, handleChange }">
                <label class="block mb-1 font-semibold">Người chiến thắng</label>
                <Select
                  :modelValue="field.value"
                  @update:modelValue="handleChange"
                  :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                  :options="matchPlayersOptions"
                  optionLabel="label"
                  optionValue="value"
                  placeholder="Chọn người thắng"
                  showClear
                  class="w-full"
                />
                <ErrorMessage class="p-error" name="winner_id" />
              </Field>
            </div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
            <Button type="button" label="Hủy" icon="pi pi-times" @click="localVisible = false" class="p-button-text" />
            <Button type="submit" label="Lưu" icon="pi pi-check" :loading="form.processing" autofocus />
          </div>
        </div>
      </form>
    </VeeForm>
  </Dialog>
</template>
