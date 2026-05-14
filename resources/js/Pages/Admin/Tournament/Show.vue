<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { ref, computed } from 'vue';
import moment from 'moment';
import Swal from 'sweetalert2';

const page = usePage();
const tournament = computed(() => page.props.value.data.tournament);
const participants = computed(() => page.props.value.data.participants);
const matches = computed(() => page.props.value.data.matches);

const getWinnerMatches = () => {
  return matches.value.filter(m => m.bracket_type === 0);
};

const getLoserMatches = () => {
  return matches.value.filter(m => m.bracket_type === 1);
};

// Participant logic
const updateParticipantForm = useForm({ status: 0 });
const updateParticipant = (participantId, newStatus) => {
  updateParticipantForm.status = newStatus;
  updateParticipantForm.post(route('admin.tournament.participant.update', { tournament: tournament.value.id, participant: participantId }), {
    preserveScroll: true
  });
};

const getParticipantStatusLabel = (status) => {
  return status === 0 ? 'Chờ duyệt' : (status === 1 ? 'Đã duyệt' : 'Từ chối');
};

const getParticipantStatusClass = (status) => {
  return status === 0 ? 'bg-yellow-100 text-yellow-800' : (status === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
};

// Match logic
const displayMatchModal = ref(false);
const isEditMatch = ref(false);
const selectedMatchId = ref(null);

const matchForm = useForm({
  round_name: '',
  player1_id: null,
  player2_id: null,
  player1_score: 0,
  player2_score: 0,
  winner_id: null,
  status: 0
});

const openCreateMatchModal = () => {
  isEditMatch.value = false;
  matchForm.reset();
  displayMatchModal.value = true;
};

const openEditMatchModal = (match) => {
  isEditMatch.value = true;
  selectedMatchId.value = match.id;
  matchForm.player1_score = match.player1_score;
  matchForm.player2_score = match.player2_score;
  matchForm.winner_id = match.winner_id;
  matchForm.status = match.status;
  displayMatchModal.value = true;
};

const submitMatchForm = () => {
  if (isEditMatch.value) {
    matchForm.put(route('admin.tournament.match.update', { tournament: tournament.value.id, match: selectedMatchId.value }), {
      onSuccess: () => { displayMatchModal.value = false; }
    });
  } else {
    matchForm.post(route('admin.tournament.match.store', tournament.value.id), {
      onSuccess: () => { displayMatchModal.value = false; }
    });
  }
};

// Approved participants for dropdowns
const approvedParticipants = computed(() => {
  return participants.value.filter(p => p.status === 1).map(p => ({
    label: p.customer.name,
    value: p.customer.id
  }));
});

const matchStatusOptions = [
  { label: 'Sắp diễn ra', value: 0 },
  { label: 'Đang đấu', value: 1 },
  { label: 'Đã xong', value: 2 }
];

const getMatchStatusClass = (status) => {
  return status === 0 ? 'bg-gray-100 text-gray-800' : (status === 1 ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800');
};

const generateBracketForm = useForm({});
const generateBracket = () => {
  Swal.fire({
    title: 'Tạo sơ đồ thi đấu tự động?',
    text: "Tất cả các trận đấu hiện tại sẽ bị xóa. Hệ thống sẽ lấy các khách hàng Đã Duyệt để chia cặp ngẫu nhiên.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Tạo ngay',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      generateBracketForm.post(route('admin.tournament.generateBracket', tournament.value.id), {
        preserveScroll: true
      });
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
              Tổng số đăng ký: <strong>{{ participants.length }}</strong> 
              (Đã duyệt: {{ participants.filter(p => p.status === 1).length }})
            </div>
            
            <div class="p-datatable p-component">
              <table role="table" class="p-datatable-table">
                <thead class="p-datatable-thead">
                  <tr>
                    <th>Tên khách hàng</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Ngày đăng ký</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                  </tr>
                </thead>
                <tbody class="p-datatable-tbody">
                  <tr v-for="participant in participants" :key="participant.id">
                    <td>{{ participant.customer?.name }}</td>
                    <td>{{ participant.customer?.email }}</td>
                    <td>{{ participant.customer?.phone }}</td>
                    <td>{{ moment(participant.created_at).format('DD/MM/YYYY HH:mm') }}</td>
                    <td>
                      <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getParticipantStatusClass(participant.status)]">
                        {{ getParticipantStatusLabel(participant.status) }}
                      </span>
                    </td>
                    <td>
                      <div class="flex gap-2">
                        <Button v-if="participant.status !== 1" @click="updateParticipant(participant.id, 1)" icon="pi pi-check" class="p-button-success p-button-sm p-button-rounded" title="Duyệt" />
                        <Button v-if="participant.status !== 2" @click="updateParticipant(participant.id, 2)" icon="pi pi-times" class="p-button-danger p-button-sm p-button-rounded" title="Từ chối" />
                      </div>
                    </td>
                  </tr>
                  <tr v-if="participants.length === 0">
                    <td colspan="6" class="text-center py-4">Chưa có khách hàng nào đăng ký</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </TabPanel>
          
          <TabPanel header="Sơ đồ thi đấu (Winner/Loser)">
            <div class="mb-6 flex gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
              <Button label="Xếp kèo đấu thủ công" icon="pi pi-plus" @click="openCreateMatchModal" class="p-button-primary p-button-sm shadow-sm" />
              <Button label="Tạo sơ đồ tự động (Double Elimination)" icon="pi pi-sitemap" @click="generateBracket" :loading="generateBracketForm.processing" class="p-button-warning p-button-sm shadow-sm" />
            </div>
            
            <div class="space-y-12">
              <!-- Winner Bracket -->
              <div v-if="getWinnerMatches().length > 0">
                <div class="flex items-center gap-3 mb-6">
                  <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
                  <h4 class="text-lg font-black text-indigo-900 uppercase tracking-wider">Nhánh Thắng (Upper Bracket)</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                  <div v-for="match in getWinnerMatches()" :key="match.id" class="bg-white border-2 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden" :class="match.status === 1 ? 'border-blue-400 ring-4 ring-blue-50' : 'border-gray-50'">
                    <div class="bg-gray-50 px-4 py-2 flex justify-between items-center border-b border-gray-100">
                      <span class="font-bold text-[10px] text-gray-500 uppercase tracking-widest">{{ match.round_name }} | #{{ match.match_number }}</span>
                      <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter', getMatchStatusClass(match.status)]">
                        {{ matchStatusOptions.find(o => o.value === match.status)?.label }}
                      </span>
                    </div>
                    <div class="p-5 flex flex-col gap-4">
                      <div class="flex justify-between items-center group">
                        <div :class="['flex-1 font-bold truncate flex items-center gap-2', match.winner_id === match.player1_id ? 'text-green-600' : 'text-gray-700']">
                           <div v-if="match.winner_id === match.player1_id" class="w-2 h-2 bg-green-500 rounded-full"></div>
                           {{ match.player1?.name || 'Đợi kết quả...' }}
                        </div>
                        <div class="text-xl font-black bg-gray-100 px-4 py-1.5 rounded-lg text-gray-800 shadow-inner font-mono">{{ match.player1_score }}</div>
                      </div>
                      <div class="h-px bg-gradient-to-r from-transparent via-gray-100 to-transparent"></div>
                      <div class="flex justify-between items-center group">
                        <div :class="['flex-1 font-bold truncate flex items-center gap-2', match.winner_id === match.player2_id ? 'text-green-600' : 'text-gray-700']">
                           <div v-if="match.winner_id === match.player2_id" class="w-2 h-2 bg-green-500 rounded-full"></div>
                           {{ match.player2?.name || 'Đợi kết quả...' }}
                        </div>
                        <div class="text-xl font-black bg-gray-100 px-4 py-1.5 rounded-lg text-gray-800 shadow-inner font-mono">{{ match.player2_score }}</div>
                      </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-50 text-right">
                      <Button label="Cập nhật kết quả" icon="pi pi-pencil" @click="openEditMatchModal(match)" class="p-button-text p-button-sm font-bold text-indigo-600" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Loser Bracket -->
              <div v-if="getLoserMatches().length > 0">
                <div class="flex items-center gap-3 mb-6">
                  <div class="w-1.5 h-6 bg-rose-600 rounded-full"></div>
                  <h4 class="text-lg font-black text-rose-900 uppercase tracking-wider">Nhánh Thua (Lower Bracket)</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                  <div v-for="match in getLoserMatches()" :key="match.id" class="bg-white border-2 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border-gray-50">
                    <div class="bg-gray-50 px-4 py-2 flex justify-between items-center border-b border-gray-100">
                      <span class="font-bold text-[10px] text-gray-500 uppercase tracking-widest">{{ match.round_name }} | #{{ match.match_number }}</span>
                    </div>
                    <div class="p-5 flex flex-col gap-4">
                      <div class="flex justify-between items-center">
                        <div :class="['flex-1 font-bold truncate', match.winner_id === match.player1_id ? 'text-green-600' : 'text-gray-700']">
                          {{ match.player1?.name || 'Chờ từ nhánh thắng...' }}
                        </div>
                        <div class="text-xl font-black bg-gray-100 px-4 py-1.5 rounded-lg font-mono">{{ match.player1_score }}</div>
                      </div>
                      <div class="flex justify-between items-center">
                        <div :class="['flex-1 font-bold truncate', match.winner_id === match.player2_id ? 'text-green-600' : 'text-gray-700']">
                          {{ match.player2?.name || 'Chờ từ nhánh thắng...' }}
                        </div>
                        <div class="text-xl font-black bg-gray-100 px-4 py-1.5 rounded-lg font-mono">{{ match.player2_score }}</div>
                      </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-50 text-right">
                      <Button label="Cập nhật kết quả" icon="pi pi-pencil" @click="openEditMatchModal(match)" class="p-button-text p-button-sm font-bold text-rose-600" />
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="matches.length === 0" class="flex flex-col items-center justify-center py-24 bg-gray-50 rounded-3xl border-4 border-dashed border-gray-200">
                <i class="pi pi-info-circle text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-400 font-bold text-lg">Chưa có lịch thi đấu. Hãy bấm "Tạo sơ đồ tự động" để bắt đầu.</p>
              </div>
            </div>
          </TabPanel>
        </TabView>
      </Panel>

      <!-- Match Modal -->
      <Dialog v-model:visible="displayMatchModal" :header="isEditMatch ? 'Cập nhật trận đấu' : 'Tạo trận đấu mới'" :style="{width: '500px'}" :modal="true">
        <form @submit.prevent="submitMatchForm" class="flex flex-col gap-4 mt-2">
          
          <div v-if="!isEditMatch" class="field">
            <label class="block mb-1 font-semibold">Tên vòng đấu</label>
            <InputText v-model="matchForm.round_name" class="w-full" placeholder="Vòng 1/16, Tứ kết..." required />
            <div class="text-red-500 text-xs mt-1" v-if="matchForm.errors?.round_name">{{ matchForm.errors.round_name }}</div>
          </div>

          <div v-if="!isEditMatch" class="field">
            <label class="block mb-1 font-semibold">Tuyển thủ 1</label>
            <Select v-model="matchForm.player1_id" :options="approvedParticipants" optionLabel="label" optionValue="value" placeholder="Chọn tuyển thủ" class="w-full" required />
            <div class="text-red-500 text-xs mt-1" v-if="matchForm.errors?.player1_id">{{ matchForm.errors.player1_id }}</div>
          </div>

          <div v-if="!isEditMatch" class="field">
            <label class="block mb-1 font-semibold">Tuyển thủ 2</label>
            <Select v-model="matchForm.player2_id" :options="approvedParticipants" optionLabel="label" optionValue="value" placeholder="Chọn tuyển thủ" class="w-full" required />
            <div class="text-red-500 text-xs mt-1" v-if="matchForm.errors?.player2_id">{{ matchForm.errors.player2_id }}</div>
          </div>

          <div v-if="isEditMatch" class="grid grid-cols-2 gap-4">
            <div class="field">
              <label class="block mb-1 font-semibold text-center">Điểm Tuyển thủ 1</label>
              <InputNumber v-model="matchForm.player1_score" showButtons buttonLayout="horizontal" :min="0" class="w-full justify-center" />
            </div>
            <div class="field">
              <label class="block mb-1 font-semibold text-center">Điểm Tuyển thủ 2</label>
              <InputNumber v-model="matchForm.player2_score" showButtons buttonLayout="horizontal" :min="0" class="w-full justify-center" />
            </div>
          </div>

          <div v-if="isEditMatch" class="field">
            <label class="block mb-1 font-semibold">Trạng thái trận đấu</label>
            <Select v-model="matchForm.status" :options="matchStatusOptions" optionLabel="label" optionValue="value" class="w-full" />
          </div>

          <div v-if="isEditMatch" class="field">
            <label class="block mb-1 font-semibold">Người chiến thắng (Nếu đã kết thúc)</label>
            <Select v-model="matchForm.winner_id" :options="approvedParticipants" optionLabel="label" optionValue="value" placeholder="Chọn người thắng" showClear class="w-full" />
          </div>

          <div class="flex justify-end gap-2 mt-4">
            <Button type="button" label="Hủy" icon="pi pi-times" @click="displayMatchModal = false" class="p-button-text" />
            <Button type="submit" label="Lưu" icon="pi pi-check" :loading="matchForm.processing" autofocus />
          </div>
        </form>
      </Dialog>
    </template>
  </AdminLayout>
</template>
