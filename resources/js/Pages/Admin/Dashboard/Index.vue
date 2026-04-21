<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import viLocale from '@fullcalendar/core/locales/vi';
import timeGridPlugin from '@fullcalendar/timegrid';
import { reactive, computed, watch, ref } from 'vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { Form as VeeForm, Field, ErrorMessage, configure } from 'vee-validate';
import moment from 'moment';
import axios from 'axios';
import { useRequestStore } from '@/store/request';
import { localize, setLocale } from '@vee-validate/i18n';
import { useToast } from 'primevue/usetoast';
import { usePage } from '@inertiajs/inertia-vue3';

const page = usePage();
const toast = useToast();
const calendarRef = ref(null);
const dialogVisible = ref(false);
const hasAutoOpened = ref(false);
const state = reactive({
  model: { event_type: 1 },
  initialView: 'dayGridMonth',
  calendarInfo: {},
  selectedDateInfo: {},
  dateSel: null,
  rawEvents: [],
  selectedColors: null
});

const handleDateClick = (arg) => {
  state.selectedDateInfo = arg;
  state.dateSel = moment(arg.dateStr).format('YYYY/MM/DD');
  state.model = {
    event_type: 1,
    start_date: {
      hours: moment(arg.dateStr).format('HH'),
      minutes: moment(arg.dateStr).format('mm')
    },
    user_ids: props.data.users.filter((u) => u.value === page.props.value.user.id)
  };
  dialogVisible.value = true;
};

const handleEventClick = (clickInfo) => {
  const event = clickInfo.event;
  openEditModal({
    id: event.id,
    title: event.title,
    extendedProps: event.extendedProps
  });
};

const openEditModal = (event) => {
  const props = event.extendedProps;
  state.dateSel = moment(props.start_date).format('YYYY/MM/DD');
  state.selectedDateInfo = {
    startStr: moment(props.start_date).format('YYYY/MM/DD HH:mm'),
    endStr: moment(props.end_date).format('YYYY/MM/DD HH:mm'),
    dateStr: moment(props.start_date).format('YYYY/MM/DD')
  };
  state.model = {
    id: event.id,
    name: event.title,
    start_date: props.event_type === 1 ? { hours: moment(props.start_date).format('HH'), minutes: moment(props.start_date).format('mm') } : moment(props.start_date).format('YYYY/MM/DD'),
    end_date: props.event_type === 1 ? { hours: moment(props.end_date).format('HH'), minutes: moment(props.end_date).format('mm') } : moment(props.end_date).format('YYYY/MM/DD'),
    event_type: props.event_type,
    location: props.location,
    note: props.note,
    private_flag: props.private_flag,
    type: props.type,
    color: props.color,
    desktop_notification_flag: props.desktop_notification_flag,
    flag_delete: props.flag_delete,
    is_complete: props.is_complete,
    user_event_user_id: props.user_event_user_id,
    user_ids: getUsersByIds(props.user_ids),
    target_date: props.target_date ? moment(props.target_date).format('YYYY/MM/DD') : null
  };
  dialogVisible.value = true;
};

const getUsersByIds = (ids) => {
  const users = usersComputed.value;
  if (!ids || !ids.length) return [users.filter((u) => u.value === page.props.value.user.id)];
  return users.filter((u) => ids.includes(u.value));
};

const removeUser = (user) => {
  state.model.user_ids = state.model.user_ids.filter((u) => u.value !== user.value);
};

const isOptionDisabled = (option) => {
  return option.value === page.props.value.user.id;
};

const onUserChange = (newValue, handleChange) => {
  const users = newValue || [];
  const currentUserId = page.props.value.user.id;
  const isSelected = users.some((u) => u.value === currentUserId);

  if (!isSelected) {
    const currentUser = props.data.users.find((u) => u.value === currentUserId);
    if (currentUser) {
      handleChange([...users, currentUser]);
      return;
    }
  }
  handleChange(users);
};

const handleDateSelect = (arg) => {
  state.selectedDateInfo = arg;
  state.dateSel = moment(arg.startStr).format('YYYY/MM/DD');
  state.model = {
    event_type: 1,
    start_date: {
      hours: moment(arg.startStr).format('HH'),
      minutes: moment(arg.startStr).format('mm')
    },
    end_date: {
      hours: moment(arg.endStr).format('HH'),
      minutes: moment(arg.endStr).format('mm')
    },
    user_ids: props.data.users.filter((u) => u.value === page.props.value.user.id)
  };
  dialogVisible.value = true;
};

const calendarOptions = reactive({
  plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
  initialView: 'dayGridMonth',
  selectable: true,
  dateClick: handleDateClick,
  eventClick: handleEventClick,
  select: handleDateSelect,
  locale: viLocale,
  timeZone: 'Asia/Ho_Chi_Minh',
  headerToolbar: {
    end: 'today prev,next',
    center: 'title',
    start: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  datesSet: async (info) => {
    fetchData(info);
    state.calendarInfo = info;
  },
  events: []
});

const toggleColor = (color) => {
  if (state.selectedColors.includes(color)) {
    state.selectedColors = state.selectedColors.filter((c) => c !== color);
  } else {
    state.selectedColors.push(color);
  }
  filterEvents();
};

const filterEvents = () => {
  if (state.selectedColors.length === 0) {
    calendarOptions.events = [];
  } else {
    calendarOptions.events = state.rawEvents.filter((event) => state.selectedColors.includes(event.extendedProps.color));
  }
};

const fetchData = async (info) => {
  useRequestStore().showLoading();
  calendarOptions.events = [];
  state.initialView = info.view.type;
  axios
    .post(route('admin.dashboard.event'), {
      start_date: moment(info.startStr).format('YYYY-MM-DD'),
      end_date: moment(info.endStr).format('YYYY-MM-DD'),
      view: info.view.type,
      type: props.data.type
    })
    .then((res) => {
      state.rawEvents = res.data.events;
      if (!state.selectedColors) {
        state.selectedColors = props.data.colors.map((c) => c.value);
        state.selectedColors.push('#b9c1c9');
      }
      filterEvents();
      useRequestStore().hideLoading();
      if (props.request && props.request.event_id && !hasAutoOpened.value) {
        const event = calendarOptions.events.find((e) => e.id == props.request.event_id);
        if (event) {
          openEditModal({
            id: event.id,
            title: event.title,
            extendedProps: event.extendedProps
          });
          hasAutoOpened.value = true;
        }
      }
    });
};

const props = defineProps({
  data: Object,
  request: Object
});
const usersComputed = computed(() => {
  return props.data.users || [];
});

const setMessageError = () => {
  let messError = {
    vi: {
      fields: {
        name: {
          required: 'Vui lòng nhập tên lịch.',
          max: 'Tên lịch không được vượt quá 255 ký tự.'
        },
        start_date: {
          required: state.model.event_type === 1 ? 'Vui lòng chọn thời gian bắt đầu.' : 'Vui lòng chọn ngày bắt đầu.'
        },
        end_date: {
          required: state.model.event_type === 1 ? 'Vui lòng chọn thời gian kết thúc.' : 'Vui lòng chọn ngày kết thúc.'
        },
        location: {
          max: 'Địa điểm không được vượt quá 255 ký tự.'
        },
        note: {
          max: 'Ghi chú không được vượt quá 1000 ký tự.'
        },
        color: {
          required: 'Vui lòng chọn màu sắc.'
        }
      }
    }
  };
  configure({
    generateMessage: localize(messError)
  });
};

setLocale('vi');
setMessageError();

watch(
  () => state.model.event_type,
  (newVal) => {
    setMessageError();
    if (newVal == 1) {
      state.model.start_date = {
        hours: moment(state.dateSel).format('HH'),
        minutes: moment(state.dateSel).format('mm')
      };
      state.model.start_date = state.selectedDateInfo.endStr
        ? {
            hours: moment(state.selectedDateInfo.endStr).format('HH'),
            minutes: moment(state.selectedDateInfo.endStr).format('mm')
          }
        : {};
    } else {
      state.model.start_date = state.dateSel;
      state.model.end_date = state.selectedDateInfo.endStr;
    }
  }
);

const onSubmit = () => {
  useRequestStore().showLoading();
  axios
    .post(route('admin.dashboard.store'), {
      ...state.model,
      user_ids: state.model.user_ids ? state.model.user_ids.map((u) => u.value) : [],
      start_date: state.model.event_type == 1 ? moment(state.dateSel + ' ' + state.model.start_date.hours + ':' + state.model.start_date.minutes).format('YYYY/MM/DD HH:mm:00') : moment(state.model.start_date).format('YYYY/MM/DD'),
      end_date: state.model.event_type == 1 ? moment(state.dateSel + ' ' + state.model.end_date.hours + ':' + state.model.end_date.minutes).format('YYYY/MM/DD HH:mm:00') : moment(state.model.end_date).format('YYYY/MM/DD'),
      target_date: state.model.target_date ? moment(state.model.target_date).format('YYYY/MM/DD') : null,
      type: props.data.type
    })
    .then((res) => {
      useRequestStore().hideLoading();
      toast.add({ severity: 'success', summary: res.data.message, life: 3000 });
      dialogVisible.value = false;
      fetchData(state.calendarInfo);
    })
    .catch((error) => {
      useRequestStore().hideLoading();
      toast.add({ severity: 'error', summary: error.response?.data?.message || 'Error', life: 3000 });
    });
};

const deleteEvent = () => {
  useRequestStore().showLoading();
  axios
    .delete(route('admin.dashboard.destroy', state.model.id))
    .then((res) => {
      useRequestStore().hideLoading();
      toast.add({ severity: 'success', summary: res.data.message, life: 3000 });
      dialogVisible.value = false;
      fetchData(state.calendarInfo);
    })
    .catch((error) => {
      useRequestStore().hideLoading();
      toast.add({ severity: 'error', summary: error.response?.data?.message || 'Error', life: 3000 });
    });
};

const maxDate = () => {
  if (state.model.event_type !== 1) {
    if (state.model.end_date) {
      return new Date(state.model.end_date);
    }
    return null;
  }
  if (!state.model.end_date) return null;
  if (state.model.event_type !== 1) {
    return new Date(state.model.end_date).format('YYYY/MM/DD');
  }
  if (state.model.start_date) {
    if (typeof state.model.end_date === 'object' && 'hours' in state.model.end_date) {
      const h = state.model.end_date.hours ?? 0;
      const m = state.model.end_date.minutes ?? 0;
      return new Date(`${state.dateSel} ${h}:${m}`);
    } else if (typeof state.model.end_date === 'string') {
      const mDate = moment(state.model.end_date);
      if (mDate.isValid()) {
        return mDate.toDate();
      }
    }
    return moment(state.dateSel + ' ' + state.model.end_date).format('YYYY/MM/DD HH:mm');
  }
  return moment(state.dateSel + ' ' + state.model.end_date).format('YYYY/MM/DD HH:mm');
};

const minDate = () => {
  if (state.model.event_type !== 1) {
    if (state.model.start_date) {
      return new Date(state.model.start_date);
    }
    return null;
  }
  if (state.model.event_type === 1) {
    if (state.model.start_date) {
      if (typeof state.model.start_date === 'object' && 'hours' in state.model.start_date) {
        const h = state.model.start_date.hours ?? 0;
        const m = state.model.start_date.minutes ?? 0;
        return new Date(`${state.dateSel} ${h}:${m}`);
      } else if (typeof state.model.start_date === 'string') {
        const mDate = moment(state.model.start_date);
        if (mDate.isValid()) {
          return mDate.toDate();
        }
      }
    }
    return moment(state.dateSel + ' ' + state.model.start_date).format('YYYY/MM/DD HH:mm');
  }
  return moment(state.dateSel).format('YYYY/MM/DD');
};

const getTimeObject = (dateValue) => {
  if (!dateValue) return null;
  if (typeof dateValue === 'object' && 'hours' in dateValue) {
    return dateValue;
  }
  const m = moment(dateValue);
  if (m.isValid()) {
    return { hours: m.hours(), minutes: m.minutes() };
  }
  return null;
};
</script>

<template>
  <AdminLayout>
    <template #content>
      <div class="flex items-center gap-2 mb-4 bg-white p-3">
        <span class="font-bold text-gray-700 mr-2">Bộ lọc:</span>
        <div
          v-for="color in $page.props.data.colors"
          :key="color.value"
          @click="toggleColor(color.value)"
          class="w-6 h-6 rounded-full cursor-pointer transition-all duration-200 flex items-center justify-center border-2"
          :class="[state.selectedColors && state.selectedColors.includes(color.value) ? 'ring-2 ring-offset-2 ring-blue-400 transform scale-110' : 'opacity-50 hover:opacity-100']"
          :style="{ backgroundColor: color.value, borderColor: color.value }"
        >
          <i v-if="state.selectedColors && state.selectedColors.includes(color.value)" class="pi pi-check text-white text-xs font-bold filter-check"></i>
        </div>
        <div
          @click="toggleColor('#b9c1c9')"
          class="w-6 h-6 rounded-full cursor-pointer transition-all duration-200 flex items-center justify-center border-2"
          :class="[state.selectedColors && state.selectedColors.includes('#b9c1c9') ? 'ring-2 ring-offset-2 ring-blue-400 transform scale-110' : 'opacity-50 hover:opacity-100']"
          :style="{ backgroundColor: '#b9c1c9', borderColor: '#b9c1c9' }"
        >
          <i v-if="state.selectedColors && state.selectedColors.includes('#b9c1c9')" class="pi pi-check text-white text-xs font-bold filter-check"></i>
        </div>
      </div>
      <FullCalendar ref="calendarRef" :options="calendarOptions">
        <template #eventContent="arg">
          <div class="flex flex-col w-full overflow-hidden">
            <div class="flex justify-between items-start">
              <div class="fc-event-time" v-if="arg.timeText">{{ arg.timeText }}</div>
            </div>
            <div class="fc-event-title font-bold">{{ arg.event.title }}</div>
            <div class="flex items-center -space-x-2 mt-1" v-if="arg.event.extendedProps.avatar_urls && arg.event.extendedProps.avatar_urls.length">
              <template v-for="(avatar, index) in arg.event.extendedProps.avatar_urls" :key="index">
                <img v-if="avatar" :src="avatar" class="w-6 h-6 rounded-full border border-white bg-gray-200" style="object-fit: cover" />
                <div v-else class="w-6 h-6 rounded-full border border-white bg-gray-300 flex items-center justify-center text-xs text-white">
                  <i class="pi pi-user"></i>
                </div>
              </template>
            </div>
          </div>
        </template>
      </FullCalendar>
      <Dialog v-model:visible="dialogVisible" modal header="Lịch làm việc" :dismissableMask="true" :style="{ width: '50vw' }" :breakpoints="{ '960px': '90vw', '640px': '95vw' }">
        <VeeForm as="div" v-slot="{ handleSubmit }">
          <form @submit="handleSubmit($event, onSubmit)" id="event-form" class="form-data">
            <div class="flex flex-col gap-3">
              <div class="flex flex-wrap gap-3">
                <div class="flex flex-wrap mt-2">
                  <div class="flex flex-wrap">
                    <RadioButton :readonly="state.model.id != null" v-model="state.model.event_type" :value="1" inputId="event_type_1" />
                    <label for="event_type_1" class="ml-1 mt-1">Theo giờ</label>
                  </div>
                  <div class="flex flex-wrap ml-2">
                    <RadioButton :readonly="state.model.id != null" v-model="state.model.event_type" :value="2" inputId="event_type_2" />
                    <label for="event_type_2" class="ml-1 mt-1">Theo ngày</label>
                  </div>
                </div>
              </div>
              <div class="flex flex-col mt-2">
                <Field name="name" rules="required|max:255" v-model="state.model.name" v-slot="{ field, meta: metaField, handleChange }">
                  <FloatLabel variant="on">
                    <InputText :readonly="state.model.id != null && !state.model.flag_delete" class="w-full" :modelValue="field.value" @update:modelValue="handleChange" v-bind="field" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                    <label :for="field.name">Tên lịch<span class="text-red-500">*</span></label>
                  </FloatLabel>
                  <ErrorMessage class="text-red-500 text-sm mt-1" :name="field.name" />
                </Field>
              </div>
              <div class="flex gap-3 w-full">
                <div class="flex-1">
                  <Field name="start_date" rules="required" v-model="state.model.start_date" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <VueDatePicker
                        :modelValue="field.value"
                        :readonly="state.model.id && !state.model.flag_delete"
                        v-on:update:model-value="handleChange"
                        :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                        :format="state.model.event_type === 1 ? 'HH:mm' : 'yyyy/MM/dd'"
                        :enableTimePicker="state.model.event_type === 1"
                        :time-picker="state.model.event_type === 1"
                        :max-time="state.model.event_type === 1 ? getTimeObject(state.model.end_date) : null"
                        :max-date="maxDate()"
                        auto-apply
                        teleport="body"
                      />
                      <label :for="field.name">{{ state.model.event_type === 1 ? 'Thời gian bắt đầu' : 'Ngày bắt đầu' }}<span class="text-red-500">*</span></label>
                    </FloatLabel>
                    <ErrorMessage class="text-red-500 text-sm mt-1" :name="field.name" />
                  </Field>
                </div>
                <div class="flex items-center justify-center">~</div>
                <div class="flex-1">
                  <Field name="end_date" rules="required" v-model="state.model.end_date" v-slot="{ field, meta: metaField, handleChange }">
                    <FloatLabel variant="on">
                      <VueDatePicker
                        v-bind="field"
                        v-model="state.model.end_date"
                        :readonly="state.model.id && !state.model.flag_delete"
                        v-on:update:model-value="handleChange"
                        :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                        :format="state.model.event_type === 1 ? 'HH:mm' : 'yyyy/MM/dd'"
                        :enableTimePicker="state.model.event_type === 1"
                        :time-picker="state.model.event_type === 1"
                        :min-time="state.model.event_type === 1 ? getTimeObject(state.model.start_date) : null"
                        auto-apply
                        teleport="body"
                        :min-date="minDate()"
                      />
                      <label :for="field.name">{{ state.model.event_type === 1 ? 'Thời gian kết thúc' : 'Ngày kết thúc' }}<span class="text-red-500">*</span></label>
                    </FloatLabel>
                    <ErrorMessage class="text-red-500 text-sm mt-1" :name="field.name" />
                  </Field>
                </div>
              </div>

              <div class="flex-1 mt-2">
                <Field name="color" rules="required" v-model="state.model.color" v-slot="{ field, meta: metaField, handleChange }">
                  <FloatLabel variant="on">
                    <Select
                      class="w-full"
                      :name="field.name"
                      :options="$page.props.data.colors"
                      optionLabel="label_text"
                      optionValue="value"
                      :modelValue="field.value"
                      v-on:update:model-value="handleChange"
                      :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                      showClear
                      emptyMessage="Không có dữ liệu"
                    />
                    <label :for="field.name">Màu sắc<span class="text-red-500">*</span></label>
                  </FloatLabel>
                  <ErrorMessage class="text-red-500 text-sm mt-1" :name="field.name" />
                </Field>
              </div>
              <div class="flex flex-col w-full gap-3 mt-2">
                <Field name="note" rules="max:1000" v-model="state.model.note" v-slot="{ field, meta: metaField, handleChange }">
                  <FloatLabel variant="on">
                    <Textarea rows="3" class="w-full" :readonly="state.model.id && !state.model.flag_delete" :modelValue="field.value" @update:modelValue="handleChange" v-bind="field" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                    <label>Ghi chú</label>
                  </FloatLabel>
                  <ErrorMessage class="text-red-500 text-sm mt-1" :name="field.name" />
                </Field>
              </div>

              <div class="flex flex-col w-full gap-3 mt-2">
                <Field name="user_ids" v-model="state.model.user_ids" v-slot="{ field, meta: metaField, handleChange }">
                  <FloatLabel variant="on">
                    <MultiSelect
                      display="chip"
                      :disabled="state.model.id && !state.model.flag_delete"
                      :options="$page.props.data.users"
                      :optionDisabled="isOptionDisabled"
                      :modelValue="field.value"
                      v-on:update:model-value="(val) => onUserChange(val, handleChange)"
                      optionLabel="label"
                      showClear
                      emptyMessage="Không có dữ liệu"
                      :class="{ 'p-invalid': !metaField.valid && metaField.touched }"
                      class="w-full"
                    >
                      <template #option="slotProps">
                        <div class="flex items-center">
                          <img v-if="slotProps.option.avatar_url" :src="slotProps.option.avatar_url" class="w-6 h-6 rounded-full mr-2 border border-gray-200" style="object-fit: cover" />
                          <div v-else class="w-6 h-6 rounded-full mr-2 bg-gray-200 border border-white flex items-center justify-center text-xs text-white">
                            <i class="pi pi-user text-gray-500" style="font-size: 0.7rem"></i>
                          </div>
                          <div>{{ slotProps.option.label }}</div>
                        </div>
                      </template>
                      <template #chip="slotProps">
                        <div class="flex items-center mr-1">
                          <img v-if="slotProps.value.avatar_url" :src="slotProps.value.avatar_url" class="w-5 h-5 rounded-full mr-2 border border-gray-200" style="object-fit: cover" />
                          <div v-else class="w-5 h-5 rounded-full mr-2 bg-gray-200 border border-white flex items-center justify-center text-xs text-white">
                            <i class="pi pi-user text-gray-500" style="font-size: 0.6rem"></i>
                          </div>
                          <span class="mr-2">{{ slotProps.value.label }}</span>
                        </div>
                      </template>
                    </MultiSelect>
                    <label :for="field.name">Người tham gia</label>
                  </FloatLabel>
                  <ErrorMessage class="text-red-500 text-sm mt-1" :name="field.name" />
                </Field>
              </div>
            </div>

            <div class="flex justify-center items-center m-4 mt-6 gap-3">
              <Button label="Xóa" severity="danger" type="button" v-if="state.model.flag_delete" @click="deleteEvent" icon="pi pi-trash" class="btn-action" />
              <Button :label="state.model.id ? 'Cập nhật' : 'Thêm mới'" type="submit" icon="pi pi-save" class="btn-action" />
            </div>
          </form>
        </VeeForm>
      </Dialog>
    </template>
  </AdminLayout>
</template>

<style scoped>
.required {
  color: red;
  margin-left: 4px;
}
.fc-event-title,
.fc-event-time {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
/* Fix FloatLabel not floating with VueDatePicker */
:deep(.p-floatlabel:has(.dp__main)) label {
  top: -0.3rem !important;
  font-size: 12px !important;
  background: white;
  padding: 0 2px;
}
:deep(.p-floatlabel:has(.dp__main)) .dp__main {
  margin-top: 4px;
}
</style>
