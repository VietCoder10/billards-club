<template>
  <UserLayout>
    <template #content>
      <div class="p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800">{{ $page.props.data.title }}</h1>
          <Link :href="route('user.tournament.index')">
            <Button label="Quay lại" icon="pi pi-arrow-left" class="p-button-outlined" />
          </Link>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h2 class="text-xl font-bold text-blue-600">{{ $page.props.data.tournament.name }}</h2>
            <p class="text-gray-600 mt-2">{{ $page.props.data.tournament.description }}</p>
            <div class="flex gap-4 mt-3 text-sm text-gray-700">
              <span><strong>Bắt đầu:</strong> {{ moment($page.props.data.tournament.start_date).format('DD/MM/YYYY HH:mm') }}</span>
              <span><strong>Giải thưởng:</strong> {{ $page.props.data.tournament.prize_pool || 'Đang cập nhật' }}</span>
            </div>
          </div>
          <div v-if="$page.props.data.tournament.status === 1">
            <div v-if="$page.props.data.isRegistered" class="text-green-600 font-bold px-4 py-2 bg-green-50 rounded-lg">
              <i class="pi pi-check-circle"></i> Đã đăng ký
            </div>
            <Button v-else @click="register($page.props.data.tournament.id)" :loading="registerForm.processing" label="Đăng ký ngay" icon="pi pi-pencil" />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-xl overflow-hidden border border-gray-200">
          <div class="bg-gradient-to-r from-blue-700 to-indigo-800 p-5 border-b flex justify-between items-center">
            <h3 class="text-xl font-bold text-white flex items-center">
              <i class="pi pi-sitemap mr-3"></i> Sơ đồ thi đấu chuyên nghiệp
            </h3>
            <div class="flex gap-4">
              <span class="flex items-center text-white text-sm"><span class="w-3 h-3 bg-green-400 rounded-full mr-2"></span> Đã kết thúc</span>
              <span class="flex items-center text-white text-sm"><span class="w-3 h-3 bg-blue-400 rounded-full mr-2"></span> Đang đấu</span>
            </div>
          </div>
          
          <div class="p-8 overflow-x-auto bg-gray-50">
            <div v-if="Object.keys($page.props.data.rounds).length === 0" class="text-center py-20 text-gray-400 italic">
              Sơ đồ thi đấu đang được ban tổ chức cập nhật...
            </div>
            
            <div v-else class="space-y-16">
              <!-- Winner Bracket Section -->
              <div>
                <h4 class="text-indigo-900 font-black uppercase tracking-widest text-sm mb-8 border-l-4 border-indigo-600 pl-3">Nhánh Thắng (Winners Bracket)</h4>
                <div class="flex gap-12 pb-6">
                  <div v-for="(matches, roundNum) in getWinnerRounds($page.props.data.rounds)" :key="'w-'+roundNum" class="flex flex-col justify-around w-72">
                    <div class="text-center font-bold text-indigo-700 mb-6 bg-indigo-50 py-2 rounded-lg border border-indigo-100 shadow-sm">
                      {{ matches[0].round_name }}
                    </div>
                    <div class="flex flex-col gap-10 justify-around h-full py-4">
                      <div v-for="match in matches" :key="match.id" class="group transition-all duration-300">
                        <div class="bg-white border-2 rounded-xl shadow-lg overflow-hidden relative transform group-hover:-translate-y-1 group-hover:shadow-2xl transition-all" :class="{'border-blue-500 ring-2 ring-blue-200': match.status === 1, 'border-gray-100': match.status !== 1}">
                          <div class="bg-gray-50 px-3 py-1.5 text-[10px] font-bold text-gray-400 flex justify-between items-center border-b border-gray-100">
                            <span>TRẬN #{{ match.match_number }}</span>
                            <span v-if="match.status === 2" class="text-green-500 uppercase tracking-tighter">Hoàn thành</span>
                            <span v-else-if="match.status === 1" class="text-blue-500 uppercase tracking-tighter animate-pulse">Trực tiếp</span>
                          </div>
                          <div class="flex flex-col divide-y divide-gray-50">
                            <!-- Player 1 -->
                            <div class="flex justify-between items-center px-4 py-3 relative" :class="{'bg-green-50/50': match.winner_id === match.player1_id && match.status === 2}">
                              <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full" :class="match.winner_id === match.player1_id ? 'bg-green-500' : 'bg-gray-200'"></div>
                                <span class="truncate font-bold text-sm w-40" :class="match.winner_id === match.player1_id ? 'text-green-700' : 'text-gray-700'">
                                  {{ match.player1?.name || (match.status === 2 && match.winner_id ? 'Tự động lọt' : 'TBD') }}
                                </span>
                              </div>
                              <span class="font-black text-lg font-mono" :class="match.winner_id === match.player1_id ? 'text-green-600' : 'text-gray-300'">{{ match.player1_score }}</span>
                            </div>
                            <!-- Player 2 -->
                            <div class="flex justify-between items-center px-4 py-3 relative" :class="{'bg-green-50/50': match.winner_id === match.player2_id && match.status === 2}">
                              <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full" :class="match.winner_id === match.player2_id ? 'bg-green-500' : 'bg-gray-200'"></div>
                                <span class="truncate font-bold text-sm w-40" :class="match.winner_id === match.player2_id ? 'text-green-700' : 'text-gray-700'">
                                  {{ match.player2?.name || (match.status === 2 && match.winner_id ? 'Tự động lọt' : 'TBD') }}
                                </span>
                              </div>
                              <span class="font-black text-lg font-mono" :class="match.winner_id === match.player2_id ? 'text-green-600' : 'text-gray-300'">{{ match.player2_score }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Loser Bracket Section -->
              <div v-if="hasLoserMatches($page.props.data.rounds)">
                <h4 class="text-rose-900 font-black uppercase tracking-widest text-sm mb-8 border-l-4 border-rose-600 pl-3">Nhánh Thua (Losers Bracket)</h4>
                <div class="flex gap-12 pb-6">
                  <div v-for="(matches, roundNum) in getLoserRounds($page.props.data.rounds)" :key="'l-'+roundNum" class="flex flex-col justify-around w-72">
                    <div class="text-center font-bold text-rose-700 mb-6 bg-rose-50 py-2 rounded-lg border border-rose-100 shadow-sm">
                      {{ matches[0].round_name }}
                    </div>
                    <div class="flex flex-col gap-10 justify-around h-full py-4">
                      <div v-for="match in matches" :key="match.id" class="group transition-all duration-300">
                        <div class="bg-white border-2 rounded-xl shadow-lg overflow-hidden relative transform group-hover:-translate-y-1 group-hover:shadow-2xl transition-all border-gray-100">
                          <div class="bg-gray-50 px-3 py-1.5 text-[10px] font-bold text-gray-400 flex justify-between items-center border-b border-gray-100">
                            <span>TRẬN #{{ match.match_number }}</span>
                          </div>
                          <div class="flex flex-col divide-y divide-gray-50">
                            <!-- Player 1 -->
                            <div class="flex justify-between items-center px-4 py-3" :class="{'bg-green-50/50': match.winner_id === match.player1_id && match.status === 2}">
                              <span class="truncate font-bold text-sm w-40 text-gray-700">
                                {{ match.player1?.name || 'Chờ từ nhánh thắng' }}
                              </span>
                              <span class="font-black text-lg font-mono text-gray-300">{{ match.player1_score }}</span>
                            </div>
                            <!-- Player 2 -->
                            <div class="flex justify-between items-center px-4 py-3" :class="{'bg-green-50/50': match.winner_id === match.player2_id && match.status === 2}">
                              <span class="truncate font-bold text-sm w-40 text-gray-700">
                                {{ match.player2?.name || 'Chờ từ nhánh thắng' }}
                              </span>
                              <span class="font-black text-lg font-mono text-gray-300">{{ match.player2_score }}</span>
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
        </div>
      </div>
    </template>
  </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';
import { useForm, usePage, Link } from '@inertiajs/inertia-vue3';
import moment from 'moment';

const page = usePage();

const registerForm = useForm({});

const register = (tournamentId) => {
  registerForm.post(route('user.tournament.register', tournamentId), {
    preserveScroll: true
  });
};

const getWinnerRounds = (rounds) => {
  const winnerRounds = {};
  Object.keys(rounds).forEach(key => {
    const filtered = rounds[key].filter(m => m.bracket_type === 0);
    if (filtered.length > 0) winnerRounds[key] = filtered;
  });
  return winnerRounds;
};

const getLoserRounds = (rounds) => {
  const loserRounds = {};
  Object.keys(rounds).forEach(key => {
    const filtered = rounds[key].filter(m => m.bracket_type === 1);
    if (filtered.length > 0) loserRounds[key] = filtered;
  });
  return loserRounds;
};

const hasLoserMatches = (rounds) => {
  return Object.values(rounds).some(rs => rs.some(m => m.bracket_type === 1));
};
</script>
