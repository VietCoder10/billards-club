<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/inertia-vue3';
import moment from 'moment';
import { ref, computed } from 'vue';
import RegisterDialog from './Form.vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const page = usePage();

const formatDate = (date) => {
  if (!date) return '';
  return moment(date).format('DD/MM/YYYY HH:mm');
};

const formatShortDate = (date) => {
  if (!date) return '';
  return moment(date).format('DD/MM/YYYY');
};

// Active Tab for Right Column
const activeTab = ref('players');

// Selection and Dialog state
const showRegisterDialog = ref(false);
const showCancelConfirmDialog = ref(false);
const selectedTournamentId = ref(null);
const selectedTournamentName = ref('');

// Search state
const searchQuery = ref('');

const register = (tournament) => {
  selectedTournamentId.value = tournament.id;
  selectedTournamentName.value = tournament.name;
  showRegisterDialog.value = true;
};

// Unregister/Cancel Form
const cancelForm = useForm({});
const confirmCancel = () => {
  showCancelConfirmDialog.value = true;
};

const handleCancel = () => {
  cancelForm.post(route('user.tournament.cancel', page.props.value.data.tournament.id), {
    preserveScroll: true,
    onSuccess: () => {
      showCancelConfirmDialog.value = false;
    }
  });
};

// Filtered participants computed
const filteredParticipants = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  const participants = page.props.value.data.tournament.participants || [];
  if (!query) return participants;

  return participants.filter((p) => {
    const name = (p.special_name || p.customer?.name || '').toLowerCase();
    const rank = (p.rank || '').toLowerCase();
    return name.includes(query) || rank.includes(query);
  });
});

// Helper for initials
const getInitials = (name) => {
  if (!name) return 'C';
  const parts = name.split(' ');
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
};

// Color gradients for avatars based on name hash
const getAvatarGradient = (name) => {
  if (!name) return 'from-blue-400 to-indigo-500';
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  const gradients = ['from-blue-500 to-cyan-500', 'from-purple-500 to-pink-500', 'from-emerald-500 to-teal-500', 'from-amber-500 to-orange-500', 'from-indigo-500 to-purple-500', 'from-rose-500 to-red-500'];
  const index = Math.abs(hash) % gradients.length;
  return gradients[index];
};
</script>

<template>
  <UserLayout>
    <template #content>
      <div class="p-2 md:p-4 space-y-6 w-full">
        <!-- Header Page Bar -->
        <div class="flex justify-between items-center border-b border-zinc-150 dark:border-zinc-800 pb-4">
          <div class="flex items-center gap-3">
            <Link :href="route('user.tournament.index')">
              <button
                class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-650 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors shadow-sm"
              >
                <i class="pi pi-arrow-left text-sm"></i>
              </button>
            </Link>
            <div>
              <span class="text-xs font-bold text-blue-600 dark:text-blue-450 tracking-widest uppercase">CHI TIẾT GIẢI ĐẤU</span>
              <h1 class="text-xl md:text-2xl font-black text-zinc-950 dark:text-white mt-0.5 tracking-tight">
                {{ $page.props.data.tournament.name }}
              </h1>
            </div>
          </div>
        </div>

        <!-- Main Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- LEFT COLUMN: Tournament Stats & Action Button (1/3 width) -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Specs Panel -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-850 rounded-2xl shadow-sm overflow-hidden">
              <div class="bg-gradient-to-br from-indigo-500 via-blue-600 to-indigo-755 p-5 text-white relative">
                <!-- Decorative glass background -->
                <div class="absolute -right-6 -top-6 w-20 h-20 rounded-full bg-white/10 blur-lg"></div>
                <h3 class="font-bold text-lg tracking-tight relative z-10 flex items-center gap-2">
                  <i class="pi pi-info-circle"></i>
                  Thông Tin Chung
                </h3>
              </div>

              <div class="p-5 space-y-4">
                <!-- Description -->
                <div>
                  <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 tracking-wider block">MÔ TẢ CHI TIẾT</span>
                  <p class="text-sm text-zinc-650 dark:text-zinc-400 mt-1 leading-relaxed whitespace-pre-line">
                    {{ $page.props.data.tournament.description || 'Không có mô tả chi tiết cho giải đấu này.' }}
                  </p>
                </div>

                <hr class="border-zinc-100 dark:border-zinc-800" />

                <!-- Quick Specs List -->
                <div class="space-y-3.5 text-sm">
                  <!-- Status -->
                  <div class="flex justify-between items-center">
                    <span class="text-zinc-500 font-medium">Trạng thái</span>
                    <span
                      :class="[
                        'px-2.5 py-0.5 rounded-full text-xs font-bold border',
                        $page.props.data.tournament.status === 2
                          ? 'bg-blue-50 text-blue-650 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/50'
                          : 'bg-amber-50 text-amber-650 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/50'
                      ]"
                    >
                      {{ $page.props.data.tournament.status === 2 ? 'Mở đăng ký' : 'Đang diễn ra' }}
                    </span>
                  </div>

                  <!-- Date -->
                  <div class="flex justify-between items-start gap-4">
                    <span class="text-zinc-500 font-medium flex-shrink-0">Bắt đầu</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 text-right">{{ formatDate($page.props.data.tournament.start_date) }}</span>
                  </div>

                  <!-- Deadline -->
                  <div class="flex justify-between items-start gap-4">
                    <span class="text-zinc-500 font-medium flex-shrink-0">Hạn đăng ký</span>
                    <span class="font-semibold text-rose-600 dark:text-rose-400 text-right">{{ formatDate($page.props.data.tournament.registration_deadline) }}</span>
                  </div>

                  <!-- Fee -->
                  <div class="flex justify-between items-center">
                    <span class="text-zinc-500 font-medium">Lệ phí</span>
                    <span class="font-bold text-zinc-800 dark:text-zinc-200">
                      {{ $page.props.data.tournament.entry_fee > 0 ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format($page.props.data.tournament.entry_fee) : 'Miễn phí' }}
                    </span>
                  </div>

                  <!-- Prize -->
                  <div class="flex justify-between items-start gap-4">
                    <span class="text-zinc-500 font-medium flex-shrink-0">Giải thưởng</span>
                    <span class="font-bold text-amber-650 dark:text-amber-400 text-right">{{ $page.props.data.tournament.prize_pool || 'Đang cập nhật' }}</span>
                  </div>
                </div>

                <hr class="border-zinc-100 dark:border-zinc-800" />

                <!-- Registration Progress -->
                <div class="space-y-2">
                  <div class="flex items-center justify-between text-xs font-semibold text-zinc-550 dark:text-zinc-400">
                    <span class="flex items-center gap-1.5">
                      <i class="pi pi-users text-indigo-500"></i>
                      Đăng ký tham gia
                    </span>
                    <span class="text-zinc-900 dark:text-zinc-100 font-bold">
                      {{ ($page.props.data.tournament.participants || []).length }} /
                      <template v-if="$page.props.data.tournament.max_participants > 0">{{ $page.props.data.tournament.max_participants }} cơ thủ</template>
                      <template v-else>Không giới hạn</template>
                    </span>
                  </div>
                  <div class="w-full h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden relative shadow-inner">
                    <div
                      :class="[
                        'h-full rounded-full transition-all duration-700 ease-out',
                        $page.props.data.tournament.max_participants > 0 && ($page.props.data.tournament.participants || []).length >= $page.props.data.tournament.max_participants
                          ? 'bg-gradient-to-r from-rose-500 to-red-600'
                          : 'bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-650'
                      ]"
                      :style="{
                        width: $page.props.data.tournament.max_participants > 0 ? `${Math.min(100, Math.round((($page.props.data.tournament.participants || []).length / $page.props.data.tournament.max_participants) * 100))}%` : '100%'
                      }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active Registration Card / Actions -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-850 rounded-2xl p-5 shadow-sm space-y-4">
              <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 tracking-wider block uppercase">ĐĂNG KÝ CỦA BẠN</span>

              <template v-if="$page.props.data.tournament.status === 2">
                <!-- User already registered -->
                <div v-if="$page.props.data.isRegistered" class="space-y-4">
                  <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 rounded-xl text-center">
                    <i class="pi pi-check-circle text-2xl text-emerald-600 dark:text-emerald-400 mb-1.5 block"></i>
                    <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Bạn đã đăng ký tham gia!</p>
                    <p class="text-xs text-emerald-650 dark:text-emerald-400 mt-1 leading-relaxed">Thông tin của bạn đã được lưu lại. Hệ thống sẽ liên hệ xác nhận sớm nhất.</p>
                  </div>

                  <!-- Premium Cancel Button -->
                  <button
                    @click="confirmCancel"
                    type="button"
                    class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 rounded-xl text-sm font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-100/50 dark:hover:bg-rose-950/40 active:scale-98 transition-all shadow-sm"
                  >
                    <i class="pi pi-times-circle"></i>
                    Hủy đăng ký giải đấu
                  </button>
                </div>

                <!-- Registration limit reached -->
                <div
                  v-else-if="$page.props.data.tournament.max_participants > 0 && ($page.props.data.tournament.participants || []).length >= $page.props.data.tournament.max_participants"
                  class="p-4 bg-zinc-50 dark:bg-zinc-850 rounded-xl text-center border border-zinc-200 dark:border-zinc-800"
                >
                  <i class="pi pi-lock text-xl text-zinc-400 mb-1.5 block"></i>
                  <p class="text-sm font-bold text-zinc-550 dark:text-zinc-450">Giải đấu đã đầy chỗ</p>
                  <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">Rất tiếc, số lượng đăng ký đã đạt mức tối đa cho phép.</p>
                </div>

                <!-- Available for registration -->
                <button
                  v-else
                  @click="register($page.props.data.tournament)"
                  type="button"
                  class="w-full inline-flex justify-center items-center gap-2 px-4 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 active:scale-98 transition-all text-white font-extrabold rounded-xl text-sm shadow-md hover:shadow-lg"
                >
                  <i class="pi pi-pencil"></i>
                  Đăng ký tham gia ngay
                </button>
              </template>

              <!-- Ongoing status -->
              <div v-else-if="$page.props.data.tournament.status === 3" class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-xl text-center">
                <i class="pi pi-spin pi-spinner text-xl text-amber-600 dark:text-amber-400 mb-1.5 block"></i>
                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Giải đấu đang diễn ra</p>
                <p class="text-xs text-amber-650 dark:text-amber-450 mt-1">Đăng ký đã chính thức khép lại. Hãy theo dõi trực tiếp các trận đấu nhé!</p>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN: Searchable Registered Players List & Bracket (2/3 width) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Modern Tab Switcher -->
            <div class="flex bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-850 rounded-2xl p-1 shadow-sm gap-1">
              <button
                @click="activeTab = 'players'"
                type="button"
                :class="[
                  'flex-1 py-3 text-center font-extrabold text-sm rounded-xl transition-all flex items-center justify-center gap-2 active:scale-98',
                  activeTab === 'players' ? 'bg-gradient-to-r from-blue-600 to-indigo-650 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-850'
                ]"
              >
                <i class="pi pi-users text-xs"></i>
                Danh Sách Cơ Thủ
              </button>
              <button
                @click="activeTab = 'bracket'"
                type="button"
                :class="[
                  'flex-1 py-3 text-center font-extrabold text-sm rounded-xl transition-all flex items-center justify-center gap-2 active:scale-98',
                  activeTab === 'bracket' ? 'bg-gradient-to-r from-blue-600 to-indigo-650 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-850'
                ]"
              >
                <i class="pi pi-sitemap text-xs"></i>
                Sơ Đồ Thi Đấu
              </button>
            </div>

            <!-- List Body: Registered Players -->
            <div v-if="activeTab === 'players'" class="bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-850 rounded-2xl shadow-sm overflow-hidden flex flex-col h-full">
              <!-- Card Header -->
              <div class="p-5 border-b border-zinc-100 dark:border-zinc-850 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="space-y-0.5">
                  <h3 class="font-bold text-zinc-900 dark:text-white text-lg tracking-tight flex items-center gap-2">
                    <i class="pi pi-users text-indigo-500"></i>
                    Danh Sách Cơ Thủ Đăng Ký
                  </h3>
                  <p class="text-xs text-zinc-500 dark:text-zinc-500">
                    Tổng cộng: <span class="font-bold text-zinc-850 dark:text-zinc-300">{{ ($page.props.data.tournament.participants || []).length }} cơ thủ</span> đã ghi danh.
                  </p>
                </div>

                <!-- Modern Search Bar -->
                <div class="relative w-full sm:w-64">
                  <InputText
                    v-model="searchQuery"
                    type="text"
                    placeholder="Tìm cơ thủ, hạng..."
                    class="w-full text-xs py-2 pl-9 pr-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                  <span v-if="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-650 cursor-pointer">
                    <i class="pi pi-times text-xs"></i>
                  </span>
                </div>
              </div>

              <!-- List Body -->
              <div class="p-5 flex-1 min-h-[350px]">
                <!-- Empty State (No registrants at all) -->
                <div v-if="($page.props.data.tournament.participants || []).length === 0" class="flex flex-col items-center justify-center py-16 text-center space-y-4">
                  <div class="w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-550 flex items-center justify-center shadow-inner">
                    <i class="pi pi-user-plus text-3xl"></i>
                  </div>
                  <div class="space-y-1">
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-150">Chưa có ai đăng ký</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-500 max-w-xs mx-auto">Hãy là cơ thủ đầu tiên ghi danh tham gia tranh tài giải đấu này nhé!</p>
                  </div>
                  <button
                    v-if="$page.props.data.tournament.status === 2"
                    @click="register($page.props.data.tournament)"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/20 text-blue-650 dark:text-blue-400 rounded-xl text-xs font-bold shadow-sm"
                  >
                    <i class="pi pi-pencil"></i>
                    Đăng ký cơ thủ đầu tiên
                  </button>
                </div>

                <!-- Empty State (Search yields no results) -->
                <div v-else-if="filteredParticipants.length === 0" class="flex flex-col items-center justify-center py-16 text-center space-y-4">
                  <div class="w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-550 flex items-center justify-center shadow-inner">
                    <i class="pi pi-filter-slash text-2xl"></i>
                  </div>
                  <div class="space-y-1">
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-150">Không tìm thấy cơ thủ</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-500">Không tìm thấy kết quả nào trùng khớp với "{{ searchQuery }}".</p>
                  </div>
                  <button @click="searchQuery = ''" type="button" class="px-4 py-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-150 dark:hover:bg-zinc-750 text-zinc-700 dark:text-zinc-200 rounded-xl text-xs font-semibold shadow-sm transition-colors">
                    Xóa bộ lọc
                  </button>
                </div>

                <!-- Beautiful Modern Table -->
                <div v-else class="overflow-x-auto rounded-xl border border-zinc-100 dark:border-zinc-800">
                  <table class="w-full text-left border-collapse text-sm">
                    <thead>
                      <tr class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-100 dark:border-zinc-800">
                        <th class="py-3 px-4 font-bold text-xs text-zinc-450 dark:text-zinc-500 uppercase w-12 text-center">STT</th>
                        <th class="py-3 px-4 font-bold text-xs text-zinc-450 dark:text-zinc-500 uppercase">Cơ Thủ</th>
                        <th class="py-3 px-4 font-bold text-xs text-zinc-450 dark:text-zinc-500 uppercase">Hạng / Cấp Độ</th>
                        <th class="py-3 px-4 font-bold text-xs text-zinc-450 dark:text-zinc-500 uppercase text-center">Trạng Thái</th>
                        <th class="py-3 px-4 font-bold text-xs text-zinc-450 dark:text-zinc-500 uppercase text-right">Ngày Đăng Ký</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                      <tr v-for="(participant, idx) in filteredParticipants" :key="participant.id" class="hover:bg-zinc-50/50 dark:hover:bg-zinc-850/30 transition-colors">
                        <!-- Order number -->
                        <td class="py-3 px-4 text-center font-semibold text-zinc-500 text-xs">
                          {{ idx + 1 }}
                        </td>

                        <!-- Name & Avatar -->
                        <td class="py-3 px-4">
                          <div class="flex items-center gap-3">
                            <!-- Circular Gradient Avatar -->
                            <div :class="['w-8 h-8 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-extrabold text-xs shadow-sm flex-shrink-0', getAvatarGradient(participant.special_name || participant.customer?.name)]">
                              {{ getInitials(participant.special_name || participant.customer?.name) }}
                            </div>
                            <div class="min-w-0">
                              <span class="font-bold text-zinc-800 dark:text-zinc-200 block truncate leading-tight">
                                {{ participant.special_name || participant.customer?.name }}
                              </span>
                              <span class="text-[10px] font-medium text-zinc-450 dark:text-zinc-500 block mt-0.5 leading-none"> ID: {{ participant.customer?.id || 'N/A' }} </span>
                            </div>
                          </div>
                        </td>

                        <!-- Rank -->
                        <td class="py-3 px-4">
                          <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-400 border border-indigo-150 dark:border-indigo-900/30">
                            {{ participant.rank || 'Nghiệp dư' }}
                          </span>
                        </td>

                        <!-- Approval Status Badge -->
                        <td class="py-3 px-4 text-center">
                          <span
                            v-if="participant.status === 2"
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-650 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30"
                          >
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Đã duyệt
                          </span>
                          <span
                            v-else-if="participant.status === 3"
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/20 text-rose-650 dark:text-rose-450 border border-rose-200 dark:border-rose-900/30"
                          >
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Từ chối
                          </span>
                          <span v-else class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/20 text-amber-650 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Chờ duyệt
                          </span>
                        </td>

                        <!-- Registered Date -->
                        <td class="py-3 px-4 text-right font-medium text-zinc-500 text-xs">
                          {{ formatShortDate(participant.created_at) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Bracket Body -->
            <div v-else-if="activeTab === 'bracket'" class="bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-850 rounded-2xl shadow-sm overflow-hidden flex flex-col p-5 space-y-4">
              <div class="border-b border-zinc-100 dark:border-zinc-850 pb-4">
                <h3 class="font-bold text-zinc-900 dark:text-white text-lg tracking-tight flex items-center gap-2">
                  <i class="pi pi-sitemap text-indigo-500"></i>
                  Sơ Đồ Thi Đấu Giải Đấu
                </h3>
                <p class="text-xs text-zinc-505 dark:text-zinc-500">Theo dõi hành trình tranh tài của các cơ thủ trong giải đấu này.</p>
              </div>

              <!-- Bracket Empty State -->
              <div v-if="($page.props.data.rounds || []).length === 0" class="flex flex-col items-center justify-center py-16 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-850 text-zinc-400 dark:text-zinc-550 flex items-center justify-center shadow-inner">
                  <i class="pi pi-sitemap text-2xl"></i>
                </div>
                <div class="space-y-1">
                  <h4 class="font-bold text-zinc-900 dark:text-zinc-150">Chưa có sơ đồ thi đấu</h4>
                  <p class="text-xs text-zinc-500 dark:text-zinc-500 max-w-xs mx-auto">Ban tổ chức đang chuẩn bị sơ đồ ghép cặp. Hãy kiểm tra lại sau khi danh sách đăng ký được chốt!</p>
                </div>
              </div>

              <!-- Bracket Graphic Tree -->
              <div v-else class="overflow-x-auto py-6 bg-zinc-50/20 dark:bg-zinc-950/20 rounded-2xl border border-zinc-100 dark:border-zinc-800" style="min-height: 480px">
                <div class="flex gap-10 px-6" style="width: max-content; min-width: 100%">
                  <div v-for="round in $page.props.data.rounds" :key="round.id" class="flex flex-col gap-6 w-[260px]">
                    <!-- Round Title -->
                    <div class="font-bold text-center border-b border-zinc-150 dark:border-zinc-800 pb-2 text-zinc-800 dark:text-zinc-200 bg-zinc-50 dark:bg-zinc-900 rounded-xl py-2 px-3 shadow-sm text-xs tracking-wider uppercase">
                      {{ round.name }}
                    </div>

                    <!-- Round Matches list -->
                    <div class="flex-1 flex flex-col justify-around gap-6 py-4">
                      <div
                        v-for="match in round.matches"
                        :key="match.id"
                        class="border border-zinc-150 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden transition-all duration-300 hover:border-indigo-500/50 hover:shadow-md"
                      >
                        <!-- Match Header info -->
                        <div class="bg-zinc-50/50 dark:bg-zinc-900/50 px-3 py-2 flex justify-between items-center text-[10px] text-zinc-400 dark:text-zinc-550 border-b border-zinc-100 dark:border-zinc-850">
                          <span class="font-semibold">Trận #{{ match.id }}</span>
                          <span
                            :class="[
                              'px-2 py-0.5 rounded-full font-bold uppercase tracking-wider',
                              match.status === 1
                                ? 'bg-zinc-100 dark:bg-zinc-850 text-zinc-500'
                                : match.status === 2
                                ? 'bg-blue-50/50 dark:bg-blue-950/20 text-blue-550 border border-blue-200/50 dark:border-blue-900/30'
                                : 'bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-600 border border-emerald-200/50 dark:border-emerald-900/30'
                            ]"
                          >
                            {{ match.status === 1 ? 'Chờ đấu' : match.status === 2 ? 'Đang đấu' : 'Đã xong' }}
                          </span>
                        </div>

                        <!-- Match Players and scores -->
                        <div class="p-3 space-y-2.5">
                          <!-- Player 1 -->
                          <div
                            class="flex justify-between items-center text-xs"
                            :class="[
                              match.winner_id && match.winner_id === match.player1_id ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-zinc-700 dark:text-zinc-300',
                              match.winner_id && match.winner_id !== match.player1_id ? 'text-zinc-400 dark:text-zinc-600 line-through' : ''
                            ]"
                          >
                            <span class="truncate pr-2 flex items-center gap-1.5">
                              <i v-if="match.winner_id && match.winner_id === match.player1_id" class="pi pi-check-circle text-[11px] text-emerald-500"></i>
                              {{ match.player1?.special_name || match.player1?.customer?.name || 'Chờ xác định' }}
                            </span>
                            <span class="font-mono font-bold text-sm">{{ match.player1_score }}</span>
                          </div>

                          <div class="h-px bg-zinc-100 dark:bg-zinc-800"></div>

                          <!-- Player 2 -->
                          <div
                            class="flex justify-between items-center text-xs"
                            :class="[
                              match.winner_id && match.winner_id === match.player2_id ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-zinc-700 dark:text-zinc-300',
                              match.winner_id && match.winner_id !== match.player2_id ? 'text-zinc-450 dark:text-zinc-600 line-through' : ''
                            ]"
                          >
                            <span class="truncate pr-2 flex items-center gap-1.5">
                              <i v-if="match.winner_id && match.winner_id === match.player2_id" class="pi pi-check-circle text-[11px] text-emerald-500"></i>
                              {{ match.player2?.special_name || match.player2?.customer?.name || 'Chờ xác định' }}
                            </span>
                            <span class="font-mono font-bold text-sm">{{ match.player2_score }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Registration Modal Dialog -->
        <RegisterDialog v-model:visible="showRegisterDialog" :tournamentId="selectedTournamentId" :tournamentName="selectedTournamentName" />

        <!-- Unregister/Cancel Confirmation Dialog (PrimeVue Modal) -->
        <Dialog v-model:visible="showCancelConfirmDialog" header="Xác nhận hủy đăng ký" modal class="w-[90vw] md:w-[420px]" :breakpoints="{ '960px': '60vw', '640px': '90vw' }">
          <div class="space-y-5 pt-3">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 flex items-center justify-center text-rose-600 dark:text-rose-400 flex-shrink-0 shadow-sm">
                <i class="pi pi-exclamation-triangle text-xl"></i>
              </div>
              <div class="min-w-0">
                <h4 class="font-extrabold text-zinc-900 dark:text-zinc-100 text-base leading-tight">Bạn chắc chắn muốn hủy đăng ký?</h4>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">Hành động này sẽ hủy hoàn toàn đăng ký tham gia giải đấu của bạn. Bản ghi đăng ký sẽ được xóa và vị trí thi đấu sẽ được mở cho các cơ thủ khác đăng ký.</p>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
              <Button label="Quay lại" icon="pi pi-times" class="p-button-text p-button-secondary text-sm font-semibold" @click="showCancelConfirmDialog = false" :disabled="cancelForm.processing" />
              <Button label="Xác nhận hủy" icon="pi pi-check-circle" class="p-button-danger p-button-raised text-sm font-extrabold" @click="handleCancel" :loading="cancelForm.processing" />
            </div>
          </div>
        </Dialog>
      </div>
    </template>
  </UserLayout>
</template>
