<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/inertia-vue3';
import { ref, computed } from 'vue';
import moment from 'moment';
import Swal from 'sweetalert2';

const props = defineProps(['data']);
const tournament = computed(() => props.data.tournament);
const participants = computed(() => props.data.participants);

// Participant logic
const updateParticipantForm = useForm({ status: 0 });
const updateParticipant = (participantId, newStatus) => {
  updateParticipantForm.status = newStatus;
  updateParticipantForm.post(route('admin.tournament.participant.update', { tournament: tournament.value.id, participant: participantId }), {
    preserveScroll: true
  });
};

const getParticipantStatusLabel = (status) => {
  return status === 0 ? 'Chờ duyệt' : status === 1 ? 'Đã duyệt' : 'Từ chối';
};

const getParticipantStatusClass = (status) => {
  return status === 0 ? 'bg-yellow-100 text-yellow-800' : status === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
};
</script>

<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <template #icons>
          <Link :href="$page.props.data.urlBack">
            <Button label="Quay lại" icon="pi pi-arrow-left" class="btn-action"></Button>
          </Link>
        </template>

        <TabView>
          <TabPanel header="Danh sách đăng ký">
            <div class="mb-4 text-gray-600">
              Tổng số đăng ký: <strong>{{ participants.length }}</strong> (Đã duyệt: {{ participants.filter((p) => p.status === 1).length }})
            </div>

            <div class="p-datatable p-component">
              <DataTable class="w-full tbl-form" :tableStyle="{ minWidth: '100%', width: '100%', tableLayout: 'fixed' }" :value="props.data.participants">
                <ColumnGroup type="header">
                  <Row>
                    <Column header="Tên khách hàng" />
                    <Column header="Email" />
                    <Column header="SĐT" />
                    <Column header="Ngày đăng ký" />
                    <Column header="Trạng thái" />
                    <Column header="Hành động" />
                  </Row>
                </ColumnGroup>
                <Column>
                  <template #body="{ data }">
                    {{ data.customer.name }}
                  </template>
                </Column>
                <Column>
                  <template #body="{ data }">
                    {{ data.customer.email }}
                  </template>
                </Column>
                <Column>
                  <template #body="{ data }">
                    {{ data.customer.phone }}
                  </template>
                </Column>
                <Column>
                  <template #body="{ data }">
                    {{ moment(data.created_at).format('DD/MM/YYYY HH:mm') }}
                  </template>
                </Column>
                <Column>
                  <template #body="{ data }">
                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getParticipantStatusClass(data.status)]">
                      {{ getParticipantStatusLabel(data.status) }}
                    </span>
                  </template>
                </Column>
                <Column>
                  <template #body="{ data }">
                    <div>
                      <Button v-if="data.status !== 1" @click="updateParticipant(data.id, 1)" icon="pi pi-check" class="p-button-success p-button-sm p-button-rounded" title="Duyệt" />
                      <Button v-if="data.status !== 2" @click="updateParticipant(data.id, 2)" icon="pi pi-times" class="p-button-danger p-button-sm p-button-rounded" title="Từ chối" />
                    </div>
                  </template>
                </Column>
              </DataTable>
            </div>
          </TabPanel>
        </TabView>
      </Panel>
    </template>
  </AdminLayout>
</template>
