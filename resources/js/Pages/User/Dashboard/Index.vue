<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import viLocale from '@fullcalendar/core/locales/vi';
import timeGridPlugin from '@fullcalendar/timegrid';
import { reactive, ref } from 'vue';

const calendarOptions = reactive({
  plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
  initialView: 'dayGridMonth',
  selectable: true,
  locale: viLocale,
  timeZone: 'Asia/Ho_Chi_Minh',
  headerToolbar: {
    end: 'today prev,next',
    center: 'title',
    start: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  events: []
});

const props = defineProps({
  data: Object,
});
</script>

<template>
  <UserLayout>
    <template #content>
      <div class="card">
        <FullCalendar :options="calendarOptions" />
      </div>
    </template>
  </UserLayout>
</template>

<style scoped>
:deep(.fc) {
  height: calc(100vh - 200px);
}
</style>
