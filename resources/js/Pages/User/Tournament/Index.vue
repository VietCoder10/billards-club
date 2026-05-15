<script setup>
import UserLayout from '@/Layouts/User/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import moment from 'moment';

const page = usePage();

const formatDate = (date) => {
  if (!date) return '';
  return moment(date).format('DD/MM/YYYY HH:mm');
};

const registerForm = useForm({});

const register = (tournamentId) => {
  registerForm.post(route('user.tournament.register', tournamentId), {
    preserveScroll: true
  });
};
</script>
<template>
  <UserLayout>
    <template #content>
      <div class="p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">{{ $page.props.data.title }}</h1>

        <div v-if="$page.props.data.tournaments.length === 0" class="text-center py-10 bg-white rounded-lg shadow">
          <i class="pi pi-inbox text-4xl text-gray-400 mb-4 block"></i>
          <p class="text-gray-500">Hiện tại chưa có giải đấu nào đang mở.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="tournament in $page.props.data.tournaments" :key="tournament.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="bg-blue-600 p-4">
              <h2 class="text-xl font-bold text-white">{{ tournament.name }}</h2>
              <span class="inline-block mt-2 px-2 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full">
                {{ tournament.status === 1 ? 'Mở đăng ký' : 'Đang diễn ra' }}
              </span>
            </div>
            <div class="p-4 space-y-3">
              <p class="text-gray-600 text-sm line-clamp-2" :title="tournament.description">
                {{ tournament.description || 'Không có mô tả' }}
              </p>

              <div class="flex items-center text-sm text-gray-700">
                <i class="pi pi-calendar mr-2 text-blue-500"></i>
                <span><strong>Bắt đầu:</strong> {{ formatDate(tournament.start_date) }}</span>
              </div>

              <div class="flex items-center text-sm text-gray-700">
                <i class="pi pi-clock mr-2 text-red-500"></i>
                <span><strong>Hạn chót ĐK:</strong> {{ formatDate(tournament.registration_deadline) }}</span>
              </div>

              <div class="flex items-center text-sm text-gray-700">
                <i class="pi pi-money-bill mr-2 text-green-500"></i>
                <span><strong>Phí:</strong> {{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(tournament.entry_fee) }}</span>
              </div>

              <div class="flex items-center text-sm text-gray-700">
                <i class="pi pi-gift mr-2 text-yellow-500"></i>
                <span class="truncate" :title="tournament.prize_pool"><strong>Giải thưởng:</strong> {{ tournament.prize_pool || 'Đang cập nhật' }}</span>
              </div>
            </div>

            <div class="p-4 bg-gray-50 border-t flex gap-2">
              <template v-if="tournament.status === 1">
                <div v-if="tournament.is_registered" class="flex-1 text-center text-green-600 font-semibold py-2"><i class="pi pi-check-circle mr-1"></i> Đã đăng ký</div>
                <Button v-else @click="register(tournament.id)" :loading="registerForm.processing" class="flex-1 p-button-primary" label="Đăng ký" icon="pi pi-pencil" />
              </template>
              <div v-else-if="tournament.status === 2" class="flex-1 text-center text-orange-600 font-semibold py-2">Đang diễn ra</div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </UserLayout>
</template>
