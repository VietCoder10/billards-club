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
</script>
