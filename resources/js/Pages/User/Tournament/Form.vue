<script setup>
import { reactive, ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { localize } from '@vee-validate/i18n';

const props = defineProps({
  visible: { type: Boolean, default: false },
  tournamentId: { type: [Number, String], default: null },
  tournamentName: { type: String, default: '' }
});

const emit = defineEmits(['update:visible']);

const localVisible = ref(props.visible);
watch(
  () => props.visible,
  (v) => {
    localVisible.value = v;
    if (v) {
      // Pre-fill with the logged-in customer's name from page props
      state.model.special_name = page.props.value?.user?.name || '';
      state.model.rank = '';
    }
  }
);
watch(localVisible, (v) => emit('update:visible', v));

const page = usePage();

const state = reactive({
  model: {
    special_name: '',
    rank: ''
  }
});

const onSubmit = () => {
  useForm(state.model).post(route('user.tournament.register', props.tournamentId), {
    preserveScroll: true,
    onSuccess: () => {
      localVisible.value = false;
    }
  });
};

const onInvalidSubmit = ({ errors }) => {
  console.log('Invalid submit errors:', errors);
};

let messError = {
  vi: {
    fields: {
      special_name: {
        required: 'Vui lòng nhập tên cơ thủ thi đấu.',
        max: 'Tên không được vượt quá 255 ký tự.'
      },
      rank: {
        required: 'Vui lòng nhập hạng/cấp độ của cơ thủ.',
        max: 'Hạng không được vượt quá 50 ký tự.'
      }
    }
  }
};

configure({
  generateMessage: localize(messError)
});
</script>

<template>
  <Dialog v-model:visible="localVisible" :header="'Đăng ký giải đấu: ' + tournamentName" modal class="w-[90vw] md:w-[480px]" :breakpoints="{'960px': '75vw', '640px': '90vw'}">
    <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
      <form @submit="handleSubmit($event, onSubmit)" id="tournament-register-form" class="space-y-6 mt-4">
        
        <!-- Player Name -->
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Tên cơ thủ <span class="text-red-500">*</span></label>
          <Field name="special_name" rules="required|max:255" v-model="state.model.special_name" v-slot="{ field, meta, handleChange }">
            <InputText
              class="w-full"
              type="text"
              v-model="state.model.special_name"
              v-on:update:model-value="handleChange"
              v-bind="field"
              :class="{ 'p-invalid': !meta.valid && meta.touched }"
              placeholder="Nhập tên cơ thủ thi đấu"
            />
            <ErrorMessage class="text-red-500 text-xs mt-1 font-medium block" name="special_name" />
          </Field>
        </div>

        <!-- Rank/Grade -->
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Hạng/Cấp độ <span class="text-red-500">*</span></label>
          <Field name="rank" rules="required|max:50" v-model="state.model.rank" v-slot="{ field, meta, handleChange }">
            <InputText
              class="w-full"
              type="text"
              v-model="state.model.rank"
              v-on:update:model-value="handleChange"
              v-bind="field"
              :class="{ 'p-invalid': !meta.valid && meta.touched }"
              placeholder="Ví dụ: Hạng A, B, Chuyên nghiệp, Nghiệp dư..."
            />
            <ErrorMessage class="text-red-500 text-xs mt-1 font-medium block" name="rank" />
          </Field>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
          <Button label="Hủy" icon="pi pi-times" class="p-button-text p-button-secondary" @click="localVisible = false" type="button" />
          <Button label="Xác nhận đăng ký" icon="pi pi-check" class="p-button-success" type="submit" />
        </div>

      </form>
    </VeeForm>
  </Dialog>
</template>

<style scoped>
:deep(.p-inputtext) {
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
}
</style>
