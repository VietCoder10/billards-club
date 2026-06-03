<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/inertia-vue3';
import { ref, computed } from 'vue';
import moment from 'moment';
import Swal from 'sweetalert2';
import { Inertia } from '@inertiajs/inertia';
import MatchCreateModal from './Partials/MatchCreateModal.vue';
import MatchEditModal from './Partials/MatchEditModal.vue';

const props = defineProps(['data']);
const tournament = computed(() => props.data.tournament);
const participants = computed(() => props.data.participants);
const rounds = computed(() => props.data.rounds || []);

// Participant logic
const updateParticipantForm = useForm({ status: 1 });
const updateParticipant = (participantId, newStatus) => {
  updateParticipantForm.status = newStatus;
  updateParticipantForm.post(route('admin.tournament.participant.update', { tournament: tournament.value.id, participant: participantId }), {
    preserveScroll: true
  });
};

const getParticipantStatusLabel = (status) => {
  return status === 1 ? 'Chờ duyệt' : status === 2 ? 'Đã duyệt' : 'Từ chối';
};

const getParticipantStatusClass = (status) => {
  return status === 1 ? 'bg-yellow-100 text-yellow-800' : status === 2 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
};

// Bracket & Match modal / selection logic
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedMatch = ref(null);

const editMatch = (match) => {
  selectedMatch.value = match;
  showEditModal.value = true;
};

const deleteMatch = (match) => {
  Swal.fire({
    title: 'Xác nhận xóa trận đấu',
    text: `Trận #${match.id} sẽ bị xóa vĩnh viễn.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      Inertia.delete(route('admin.tournament.match.destroy', { tournament: tournament.value.id, match: match.id }), {
        preserveScroll: true
      });
    }
  });
};

// Format participants for Select dropdown in modals
const participantOptions = computed(() => {
  return participants.value
    .filter((p) => p.status === 2)
    .map((p) => ({
      label: p.special_name || p.customer?.name || 'Cơ thủ',
      value: p.id
    }));
});

// Bracket actions
const generateBracket = () => {
  Swal.fire({
    title: 'Xác nhận tạo sơ đồ',
    text: 'Hệ thống sẽ xóa sơ đồ hiện tại (nếu có) và tạo mới sơ đồ ngẫu nhiên từ các tuyển thủ đã được duyệt.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Đồng ý',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      Inertia.post(route('admin.tournament.bracket.generate', tournament.value.id));
    }
  });
};

const generateNextRound = () => {
  Inertia.post(route('admin.tournament.bracket.nextRound', tournament.value.id));
};

const resetBracket = () => {
  Swal.fire({
    title: 'Xác nhận đặt lại',
    text: 'Tất cả vòng đấu và trận đấu sẽ bị xóa vĩnh viễn.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Xóa vĩnh viễn',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      Inertia.post(route('admin.tournament.bracket.reset', tournament.value.id));
    }
  });
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
              Tổng số đăng ký: <strong>{{ participants.length }}</strong> (Đã duyệt: {{ participants.filter((p) => p.status === 2).length }})
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
                      <Button v-if="data.status !== 2" @click="updateParticipant(data.id, 2)" icon="pi pi-check" class="p-button-success p-button-sm p-button-rounded mr-1" title="Duyệt" />
                      <Button v-if="data.status !== 3" @click="updateParticipant(data.id, 3)" icon="pi pi-times" class="p-button-danger p-button-sm p-button-rounded" title="Từ chối" />
                    </div>
                  </template>
                </Column>
              </DataTable>
            </div>
          </TabPanel>

          <TabPanel header="Sơ đồ thi đấu (Bracket)">
            <div class="flex flex-wrap gap-3 mb-5 justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-150 shadow-sm">
              <div class="flex gap-2">
                <Button 
                  v-if="rounds.length === 0" 
                  label="Tạo sơ đồ thi đấu (Tự động)" 
                  icon="pi pi-sitemap" 
                  class="p-button-success" 
                  @click="generateBracket" 
                />
                <Button 
                  v-if="rounds.length > 0" 
                  label="Sinh vòng tiếp theo" 
                  icon="pi pi-step-forward" 
                  class="p-button-primary" 
                  @click="generateNextRound" 
                />
                <Button 
                  v-if="rounds.length > 0" 
                  label="Xóa & Đặt lại" 
                  icon="pi pi-refresh" 
                  class="p-button-danger" 
                  @click="resetBracket" 
                />
              </div>
              <Button 
                label="Tạo trận thủ công" 
                icon="pi pi-plus" 
                class="p-button-secondary" 
                @click="showCreateModal = true" 
              />
            </div>

            <div v-if="rounds.length === 0" class="text-center py-16 text-gray-500 border border-dashed rounded-2xl bg-gray-50/50">
              <i class="pi pi-sitemap text-5xl mb-4 text-gray-300 block"></i>
              <p class="font-medium text-lg text-gray-700">Chưa có sơ đồ thi đấu</p>
              <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">Duyệt danh sách đăng ký của các tuyển thủ trước, sau đó bấm "Tạo sơ đồ thi đấu (Tự động)" để ghép cặp ngẫu nhiên.</p>
            </div>

            <div v-else class="overflow-x-auto py-6 bg-slate-50/30 rounded-2xl border" style="min-height: 480px;">
              <div class="flex gap-12 px-6" style="width: max-content; min-width: 100%;">
                <!-- Rounds Column -->
                <div 
                  v-for="round in rounds" 
                  :key="round.id" 
                  class="flex flex-col gap-6 w-[280px]"
                >
                  <div class="font-extrabold text-center border-b border-gray-200 pb-3 text-slate-800 bg-slate-100/80 rounded-xl py-2 px-3 shadow-sm text-sm tracking-wide">
                    {{ round.name }} (Vòng {{ round.round_number }})
                  </div>
                  
                  <!-- Matches inside Round -->
                  <div class="flex-1 flex flex-col justify-around gap-6 py-4">
                    <div 
                      v-for="match in round.matches" 
                      :key="match.id" 
                      class="group relative border border-gray-200 bg-white rounded-xl shadow-sm hover:shadow-md hover:border-indigo-400 active:scale-98 transition-all cursor-pointer overflow-hidden"
                      @click="editMatch(match)"
                    >
                      <!-- Match Info Header -->
                      <div class="bg-slate-50 px-3 py-2 flex justify-between items-center gap-2 text-xs text-gray-500 border-b border-gray-100">
                        <span class="font-semibold">Mã trận: #{{ match.id }}</span>
                        <div class="flex items-center gap-2">
                          <span :class="[
                            'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                            match.status === 1 ? 'bg-gray-100 text-gray-600' :
                            match.status === 2 ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                            'bg-emerald-50 text-emerald-700 border border-emerald-250'
                          ]">
                            {{ match.status === 1 ? 'Chưa đấu' : match.status === 2 ? 'Đang đấu' : 'Đã xong' }}
                          </span>
                          <Button
                            type="button"
                            icon="pi pi-trash"
                            class="p-button-danger p-button-rounded p-button-text p-button-sm !w-7 !h-7"
                            title="Xóa trận đấu"
                            @click.stop="deleteMatch(match)"
                          />
                        </div>
                      </div>

                      <!-- Match Players and Scores -->
                      <div class="p-3 space-y-2.5">
                        <!-- Player 1 -->
                        <div 
                          class="flex justify-between items-center text-sm"
                          :class="[
                            match.winner_id && match.winner_id === match.player1_id ? 'font-bold text-emerald-600' : 'text-slate-700',
                            match.winner_id && match.winner_id !== match.player1_id ? 'text-slate-400 line-through' : ''
                          ]"
                        >
                          <span class="truncate pr-2 flex items-center gap-1.5">
                            <i v-if="match.winner_id && match.winner_id === match.player1_id" class="pi pi-check-circle text-xs"></i>
                            {{ match.player1?.special_name || match.player1?.customer?.name || 'Chờ xác định' }}
                          </span>
                          <span class="font-mono font-bold text-base">{{ match.player1_score }}</span>
                        </div>
                        
                        <div class="h-px bg-slate-100"></div>

                        <!-- Player 2 -->
                        <div 
                          class="flex justify-between items-center text-sm"
                          :class="[
                            match.winner_id && match.winner_id === match.player2_id ? 'font-bold text-emerald-600' : 'text-slate-700',
                            match.winner_id && match.winner_id !== match.player2_id ? 'text-slate-400 line-through' : ''
                          ]"
                        >
                          <span class="truncate pr-2 flex items-center gap-1.5">
                            <i v-if="match.winner_id && match.winner_id === match.player2_id" class="pi pi-check-circle text-xs"></i>
                            {{ match.player2?.special_name || match.player2?.customer?.name || 'Chờ xác định' }}
                          </span>
                          <span class="font-mono font-bold text-base">{{ match.player2_score }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </TabPanel>
        </TabView>
      </Panel>

      <!-- Match Management Modals -->
      <MatchCreateModal 
        v-model:visible="showCreateModal" 
        :tournamentId="tournament.id" 
        :participants="participantOptions" 
        @success="showCreateModal = false"
      />
      <MatchEditModal 
        v-model:visible="showEditModal" 
        :tournamentId="tournament.id" 
        :match="selectedMatch" 
        :participants="participantOptions" 
        :matchStatusOptions="$page.props.data.matchStatusOptions || []"
        @success="showEditModal = false"
      />
    </template>
  </AdminLayout>
</template>
