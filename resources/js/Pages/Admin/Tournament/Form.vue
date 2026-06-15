<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { ref, onMounted, reactive, computed } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';

const page = usePage();
const state = reactive({
  model: {
    name: '',
    description: '',
    start_date: null,
    end_date: null,
    registration_deadline: null,
    max_participants: 0,
    entry_fee: 0,
    prize_pool: '',
    status: 1
  }
});

const props = defineProps(['data']);

const statusOptions = computed(() => props.data.statusOptions || []);

onMounted(() => {
  setMessageError();
  if (props.data.isEdit) {
    state.model = { ...props.data.tournament };
    // Convert date strings to Date objects for PrimeVue Calendar
    if (state.model.start_date) state.model.start_date = new Date(state.model.start_date);
    if (state.model.end_date) state.model.end_date = new Date(state.model.end_date);
    if (state.model.registration_deadline) state.model.registration_deadline = new Date(state.model.registration_deadline);
  }
});

const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if ($('[name="' + firstInputError + '"]').hasClass('hidden') || $('[name="' + firstInputError + '"]').attr('type') == 'hidden') {
    ele = $('[name="' + firstInputError + '"]').closest('div');
  }
  ele.focus();
  $('html, body').animate(
    {
      scrollTop: ele.offset().top - 150 + 'px'
    },
    500
  );
};

const setMessageError = () => {
  let messError = {
    en: {
      fields: {
        name: {
          required: 'Vui lòng nhập tên giải đấu.',
          max: 'Tên giải đấu không được vượt quá 255 ký tự.'
        },
        start_date: {
          required: 'Vui lòng chọn ngày bắt đầu.'
        },
        end_date: {
          required: 'Vui lòng chọn ngày kết thúc.'
        },
        registration_deadline: {
          required: 'Vui lòng chọn hạn đăng ký.'
        }
      }
    }
  };
  configure({
    generateMessage: localize(messError)
  });
};

const onSubmit = () => {
  // Format dates back to string before sending
  const formData = { ...state.model };

  if (props.data.isEdit) {
    useForm({
      ...formData,
      _method: 'put'
    }).post(route('admin.tournament.update', props.data.tournament.id));
    return;
  } else {
    useForm(formData).post(route('admin.tournament.store'));
  }
};
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <template #header>
          <div class="flex items-center">
            <span class="font-bold">
              {{ $page.props.data.title }}
            </span>
          </div>
        </template>
        <template #icons>
          <Link :href="$page.props.data.urlBack">
            <Button label="Hủy " icon="pi pi-arrow-left" class="btn-action"></Button>
          </Link>
          <Button label="Lưu" type="submit" form="tournament-form" icon="pi pi-save" class="btn-action ml-2" />
        </template>
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" id="tournament-form" class="form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border rounded-lg p-4 bg-gray-50 shadow-sm">
              <div class="form-group md:col-span-2">
                <label class="form-label" require>Tên giải đấu: </label>
                <div class="form-input">
                  <Field name="name" rules="required|max:255" v-model="state.model.name" v-slot="{ field, meta: metaField, handleChange }">
                    <InputText class="w-full" type="text" v-model="state.model.name" v-on:update:model-value="handleChange" v-bind="field" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                    <ErrorMessage class="p-error" name="name" />
                  </Field>
                </div>
              </div>

              <div class="form-group md:col-span-2">
                <label class="form-label">Mô tả/Thể lệ: </label>
                <div class="form-input">
                  <Textarea class="w-full" v-model="state.model.description" rows="4" />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" require>Ngày bắt đầu: </label>
                <div class="form-input">
                  <Field name="start_date" rules="required" v-model="state.model.start_date" v-slot="{ field, meta: metaField, handleChange }">
                    <Calendar class="w-full" v-model="state.model.start_date" showTime hourFormat="24" dateFormat="dd/mm/yy" v-bind="field" @update:modelValue="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                    <ErrorMessage class="p-error" name="start_date" />
                  </Field>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" require>Ngày kết thúc: </label>
                <div class="form-input">
                  <Field name="end_date" rules="required" v-model="state.model.end_date" v-slot="{ field, meta: metaField, handleChange }">
                    <Calendar class="w-full" v-model="state.model.end_date" showTime hourFormat="24" dateFormat="dd/mm/yy" v-bind="field" @update:modelValue="handleChange" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                    <ErrorMessage class="p-error" name="end_date" />
                  </Field>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" require>Hạn chót đăng ký: </label>
                <div class="form-input">
                  <Field name="registration_deadline" rules="required" v-model="state.model.registration_deadline" v-slot="{ field, meta: metaField, handleChange }">
                    <Calendar
                      class="w-full"
                      v-model="state.model.registration_deadline"
                      showTime
                      hourFormat="24"
                      dateFormat="dd/mm/yy"
                      v-bind="field"
                      @update:modelValue="handleChange"
                      :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                    />
                    <ErrorMessage class="p-error" name="registration_deadline" />
                  </Field>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Trạng thái: </label>
                <div class="form-input">
                  <Select class="w-full" :options="statusOptions" optionLabel="label" optionValue="value" v-model="state.model.status" />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Số người tối đa (0 = Không giới hạn): </label>
                <div class="form-input">
                  <InputNumber class="w-full" v-model="state.model.max_participants" :min="0" />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Phí tham gia (VNĐ): </label>
                <div class="form-input">
                  <InputNumber class="w-full" v-model="state.model.entry_fee" :min="0" />
                </div>
              </div>

              <div class="form-group md:col-span-2">
                <label class="form-label">Cơ cấu giải thưởng: </label>
                <div class="form-input">
                  <InputText class="w-full" v-model="state.model.prize_pool" placeholder="VD: Nhất: 5tr, Nhì: 2tr" />
                </div>
              </div>
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>
