<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import jaLocale from '@fullcalendar/core/locales/ja';
import timeGridPlugin from '@fullcalendar/timegrid';
import { reactive, computed, onMounted, watch } from 'vue';
import { Form as VeeForm, Field, ErrorMessage, configure } from 'vee-validate';
import moment from 'moment';
import axios from 'axios';
import { useRequestStore } from '@/store/request';
import { ref } from 'vue';

const calendarRef = ref(null);

const calendarOptions = reactive({
  plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
  // initialView: 'timeGridDay',
  // initialView: 'timeGridWeek',
  initialView: 'dayGridMonth',
  locale: jaLocale,
  timeZone: 'Asia/Tokyo',
  headerToolbar: {
    end: 'today prev,next',
    center: 'title',
    start: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  datesSet: async (info) => {
    // fetchData(info);
  },
  events: []
});
// const fetchData = async (info) => {
//   useRequestStore().showLoading();
//   calendarOptions.events = [];
//   state.initialView = info.view.type;
//   axios
//     .post(route('admin.dashboard.store'), {
//       start_date: moment(info.startStr).format('YYYY-MM-DD'),
//       end_date: moment(info.endStr).format('YYYY-MM-DD'),
//       view: info.view.type,
//       user_id: state.model.user_id
//     })
//     .then((res) => {
//       state.statistical = res.data;
//       calendarOptions.events = res.data.events;
//       useRequestStore().hideLoading();
//     });
// };
const state = reactive({
  model: {},
  initialView: 'dayGridMonth',
  statistical: null
});
const props = defineProps({
  data: Object
});
watch(
  () => state.model.user_id,
  (newVal) => {
    setTimeout(() => {
      const calendarApi = calendarRef.value?.getApi();
      const view = calendarApi.view;
      calendarApi.changeView(view.type, view.currentStart);
    }, 100);
  }
);
</script>
<template>
  <AdminLayout>
    <template #content>
      <FullCalendar ref="calendarRef" :options="calendarOptions" />
    </template>
  </AdminLayout>
</template>
<style scoped lang="scss">
.report {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  position: relative;
  .report-item {
    width: calc((100% - 2rem) / 3);
    flex: 1 1 calc(33.333% - 10px); /* 3 item trên 1 hàng */
    background: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }
}
</style>
