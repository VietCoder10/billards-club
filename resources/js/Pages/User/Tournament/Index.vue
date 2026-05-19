<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/inertia-vue3';
import moment from 'moment';
import { ref } from 'vue';
import RegisterDialog from './Form.vue';

const page = usePage();

const formatDate = (date) => {
  if (!date) return '';
  return moment(date).format('DD/MM/YYYY HH:mm');
};

const showRegisterDialog = ref(false);
const selectedTournamentId = ref(null);
const selectedTournamentName = ref('');

const register = (tournament) => {
  selectedTournamentId.value = tournament.id;
  selectedTournamentName.value = tournament.name;
  showRegisterDialog.value = true;
};
</script>

<template>
  <UserLayout>
    <template #content>
      <div class="p-2 md:p-4 space-y-8 w-full">
        <!-- Modern Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-zinc-150 dark:border-zinc-800 pb-6">
          <div>
            <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
              <i class="pi pi-trophy text-amber-500 text-3xl"></i>
              {{ $page.props.data.title }}
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1.5 text-sm">
              Tham gia các giải đấu hấp dẫn, cọ xát nâng cao trình độ và nhận giải thưởng lớn.
            </p>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="$page.props.data.tournaments.length === 0" class="text-center py-16 bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-2xl shadow-sm">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 mb-4 shadow-inner">
            <i class="pi pi-inbox text-3xl"></i>
          </div>
          <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Không có giải đấu</h3>
          <p class="text-zinc-500 dark:text-zinc-400 mt-1 max-w-xs mx-auto text-sm">
            Hiện tại chưa có giải đấu nào đang mở đăng ký hoặc diễn ra.
          </p>
        </div>

        <!-- Tournaments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="tournament in $page.props.data.tournaments" 
            :key="tournament.id" 
            class="group relative overflow-hidden bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-150 dark:border-zinc-850 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full"
          >
            <!-- Card Header with Dynamic Gradient -->
            <div 
              :class="[
                'p-6 text-white relative overflow-hidden',
                tournament.status === 1 
                  ? 'bg-gradient-to-br from-blue-600 via-indigo-650 to-violet-750' 
                  : 'bg-gradient-to-br from-amber-500 to-orange-600'
              ]"
            >
              <!-- Decorative glass circle elements for modern background styling -->
              <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-white/10 blur-xl"></div>
              <div class="absolute -left-6 -bottom-6 w-20 h-20 rounded-full bg-white/5 blur-lg"></div>

              <div class="relative z-10 space-y-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-md text-white border border-white/20">
                  <span class="w-1.5 h-1.5 rounded-full bg-white mr-1.5 animate-pulse"></span>
                  {{ tournament.status === 1 ? 'Mở đăng ký' : 'Đang diễn ra' }}
                </span>
                
                <h2 class="text-xl font-bold tracking-tight leading-tight line-clamp-1 group-hover:text-blue-50 transition-colors" :title="tournament.name">
                  {{ tournament.name }}
                </h2>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-5">
              <!-- Description -->
              <p class="text-zinc-650 dark:text-zinc-400 text-sm leading-relaxed line-clamp-2" :title="tournament.description">
                {{ tournament.description || 'Không có mô tả chi tiết cho giải đấu này.' }}
              </p>

              <!-- Stats & Info Grid -->
              <div class="grid grid-cols-1 gap-3.5 text-sm">
                <!-- Date -->
                <div class="flex items-center text-zinc-700 dark:text-zinc-300">
                  <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center mr-3 text-blue-650 dark:text-blue-400 flex-shrink-0">
                    <i class="pi pi-calendar text-xs"></i>
                  </div>
                  <div class="min-w-0">
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500 block font-bold tracking-wider">BẮT ĐẦU</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 truncate block">{{ formatDate(tournament.start_date) }}</span>
                  </div>
                </div>

                <!-- Registration Deadline -->
                <div class="flex items-center text-zinc-700 dark:text-zinc-300">
                  <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 flex items-center justify-center mr-3 text-rose-650 dark:text-rose-400 flex-shrink-0">
                    <i class="pi pi-clock text-xs"></i>
                  </div>
                  <div class="min-w-0">
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500 block font-bold tracking-wider">HẠN ĐĂNG KÝ</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 truncate block text-rose-600 dark:text-rose-450">{{ formatDate(tournament.registration_deadline) }}</span>
                  </div>
                </div>

                <!-- Fees -->
                <div class="flex items-center text-zinc-700 dark:text-zinc-300">
                  <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center mr-3 text-emerald-650 dark:text-emerald-400 flex-shrink-0">
                    <i class="pi pi-ticket text-xs"></i>
                  </div>
                  <div class="min-w-0">
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500 block font-bold tracking-wider">LỆ PHÍ THAM GIA</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 truncate block">
                      {{ tournament.entry_fee > 0 ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(tournament.entry_fee) : 'Miễn phí' }}
                    </span>
                  </div>
                </div>

                <!-- Prize Pool -->
                <div class="flex items-center text-zinc-700 dark:text-zinc-300">
                  <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center mr-3 text-amber-650 dark:text-amber-400 flex-shrink-0">
                    <i class="pi pi-trophy text-xs"></i>
                  </div>
                  <div class="min-w-0">
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500 block font-bold tracking-wider">TỔNG GIẢI THƯỞNG</span>
                    <span class="font-semibold text-amber-600 dark:text-amber-400 truncate block">{{ tournament.prize_pool || 'Đang cập nhật' }}</span>
                  </div>
                </div>
              </div>

              <!-- Registration Progress / Ratio (Modern Display) -->
              <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold text-zinc-650 dark:text-zinc-400">
                  <span class="flex items-center gap-1.5">
                    <i class="pi pi-users text-indigo-500"></i>
                    SĨ SỐ ĐĂNG KÝ
                  </span>
                  <span class="text-zinc-900 dark:text-zinc-200 font-bold">
                    {{ tournament.participants_count }} / 
                    <template v-if="tournament.max_participants > 0">{{ tournament.max_participants }} cơ thủ</template>
                    <template v-else>Không giới hạn</template>
                  </span>
                </div>
                
                <!-- Modern Smooth Progress Bar -->
                <div class="w-full h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden relative shadow-inner">
                  <div 
                    :class="[
                      'h-full rounded-full transition-all duration-700 ease-out',
                      tournament.max_participants > 0 && tournament.participants_count >= tournament.max_participants
                        ? 'bg-gradient-to-r from-rose-500 to-red-650'
                        : 'bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-650'
                    ]"
                    :style="{
                      width: tournament.max_participants > 0 
                        ? `${Math.min(100, Math.round((tournament.participants_count / tournament.max_participants) * 100))}%` 
                        : '100%'
                    }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Card Footer / Actions -->
            <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-150 dark:border-zinc-800/80 flex gap-3">
              <!-- View Details Button -->
              <Link :href="route('user.tournament.show', tournament.id)" class="flex-1">
                <button 
                  type="button" 
                  class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm font-semibold text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors shadow-sm"
                >
                  <i class="pi pi-eye"></i>
                  Chi tiết
                </button>
              </Link>

              <!-- Registration Status / Actions -->
              <template v-if="tournament.status === 1">
                <!-- User already registered -->
                <div 
                  v-if="tournament.is_registered" 
                  class="flex-1 inline-flex justify-center items-center gap-1.5 px-4 py-2.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 rounded-xl text-sm font-bold text-emerald-600 dark:text-emerald-400 shadow-sm"
                >
                  <i class="pi pi-check-circle text-base"></i>
                  Đã đăng ký
                </div>

                <!-- Registration limit reached -->
                <button 
                  v-else-if="tournament.max_participants > 0 && tournament.participants_count >= tournament.max_participants"
                  disabled
                  type="button"
                  class="flex-1 inline-flex justify-center items-center gap-1.5 px-4 py-2.5 bg-zinc-100 dark:bg-zinc-850 rounded-xl text-sm font-semibold text-zinc-400 dark:text-zinc-500 cursor-not-allowed border border-zinc-200 dark:border-zinc-800"
                >
                  <i class="pi pi-lock"></i>
                  Đầy suất
                </button>

                <!-- Available for registration -->
                <button 
                  v-else 
                  @click="register(tournament)" 
                  type="button"
                  class="flex-1 inline-flex justify-center items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition-all text-white font-bold rounded-xl text-sm shadow-md hover:shadow-lg"
                >
                  <i class="pi pi-pencil"></i>
                  Đăng ký
                </button>
              </template>
              
              <!-- Ongoing status -->
              <div 
                v-else-if="tournament.status === 2" 
                class="flex-1 inline-flex justify-center items-center gap-1.5 px-4 py-2.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-xl text-sm font-bold text-amber-600 dark:text-amber-400 shadow-sm"
              >
                <i class="pi pi-spin pi-spinner text-base"></i>
                Đang đấu
              </div>
            </div>
          </div>
        </div>

        <!-- Registration Modal Dialog -->
        <RegisterDialog
          v-model:visible="showRegisterDialog"
          :tournamentId="selectedTournamentId"
          :tournamentName="selectedTournamentName"
        />
      </div>
    </template>
  </UserLayout>
</template>
