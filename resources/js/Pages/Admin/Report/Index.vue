<script setup>
import { ref, onMounted, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import Chart from 'primevue/chart';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
  data: Object
});

const chartData = ref();
const chartOptions = ref();

const months = ref([
  { name: 'Tháng 1', value: 1 },
  { name: 'Tháng 2', value: 2 },
  { name: 'Tháng 3', value: 3 },
  { name: 'Tháng 4', value: 4 },
  { name: 'Tháng 5', value: 5 },
  { name: 'Tháng 6', value: 6 },
  { name: 'Tháng 7', value: 7 },
  { name: 'Tháng 8', value: 8 },
  { name: 'Tháng 9', value: 9 },
  { name: 'Tháng 10', value: 10 },
  { name: 'Tháng 11', value: 11 },
  { name: 'Tháng 12', value: 12 }
]);

const currentYear = new Date().getFullYear();
const years = ref(Array.from({ length: 5 }, (_, i) => ({ name: `Năm ${currentYear - i}`, value: currentYear - i })));

const selectedMonth = ref(props.data.selectedMonth);
const selectedYear = ref(props.data.selectedYear);

const onFilterChange = () => {
  Inertia.get(
    route('admin.report.index'),
    {
      month: selectedMonth.value,
      year: selectedYear.value
    },
    {
      preserveState: true,
      replace: true
    }
  );
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const updateChart = () => {
  const documentStyle = getComputedStyle(document.documentElement);
  const textColor = documentStyle.getPropertyValue('--text-color') || '#495057';
  const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary') || '#6c757d';
  const surfaceBorder = documentStyle.getPropertyValue('--surface-border') || '#dfe7ef';

  chartData.value = {
    labels: props.data.days,
    datasets: [
      {
        label: `Doanh thu tháng ${props.data.selectedMonth}/${props.data.selectedYear}`,
        data: props.data.dailyRevenue,
        backgroundColor: documentStyle.getPropertyValue('--blue-500') || '#3B82F6',
        borderColor: documentStyle.getPropertyValue('--blue-500') || '#3B82F6',
        borderWidth: 1,
        borderRadius: 4
      }
    ]
  };

  chartOptions.value = {
    maintainAspectRatio: false,
    aspectRatio: 0.6,
    plugins: {
      legend: {
        labels: {
          color: textColor,
          font: {
            family: 'Inter, sans-serif',
            size: 14
          }
        }
      }
    },
    scales: {
      x: {
        ticks: {
          color: textColorSecondary,
          font: {
            family: 'Inter, sans-serif'
          }
        },
        grid: {
          color: surfaceBorder,
          drawBorder: false
        }
      },
      y: {
        beginAtZero: true,
        ticks: {
          color: textColorSecondary,
          font: {
            family: 'Inter, sans-serif'
          },
          callback: function (value) {
            return new Intl.NumberFormat('vi-VN').format(value);
          }
        },
        grid: {
          color: surfaceBorder,
          drawBorder: false
        }
      }
    }
  };
};

watch(
  () => props.data,
  () => {
    selectedMonth.value = props.data.selectedMonth;
    selectedYear.value = props.data.selectedYear;
    updateChart();
  },
  { deep: true }
);

onMounted(() => {
  updateChart();
});
</script>

<template>
  <AdminLayout>
    <template #content>
      <div class="card p-4">
        <div class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4 gap-3">
          <h3 class="font-bold text-2xl text-gray-800 m-0">Báo cáo doanh thu</h3>
          <div class="flex gap-2">
            <Select v-model="selectedMonth" :options="months" optionLabel="name" optionValue="value" @change="onFilterChange" class="w-10rem" />
            <Select v-model="selectedYear" :options="years" optionLabel="name" optionValue="value" @change="onFilterChange" class="w-10rem" />
          </div>
        </div>

        <div class="summary-grid">
          <div class="summary-card">
            <div class="title">Hôm nay</div>
            <div class="value text-green-500">{{ formatCurrency(data.revenueToday) }}</div>
          </div>
          <div class="summary-card">
            <div class="title">Trong tháng {{ data.selectedMonth }}/{{ data.selectedYear }}</div>
            <div class="value text-blue-500">{{ formatCurrency(data.revenueThisMonth) }}</div>
          </div>
          <div class="summary-card">
            <div class="title">Trong năm {{ data.selectedYear }}</div>
            <div class="value text-purple-500">{{ formatCurrency(data.revenueThisYear) }}</div>
          </div>
        </div>

        <div class="chart-container">
          <h5 class="text-gray-700 font-semibold mb-3">Biểu đồ doanh thu tháng {{ data.selectedMonth }}/{{ data.selectedYear }}</h5>
          <Chart type="bar" :data="chartData" :options="chartOptions" style="height: 400px" />
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<style scoped>
.summary-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-bottom: 2rem;
}
.summary-card {
  background: #ffffff;
  border: 1px solid #e1e5e9;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  flex: 1;
  min-width: 250px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  transition: transform 0.2s, box-shadow 0.2s;
}
.summary-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
}
.summary-card .title {
  font-size: 1rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.summary-card .value {
  font-size: 2.2rem;
  font-weight: 800;
  margin-top: 0.5rem;
  letter-spacing: -0.5px;
}
.chart-container {
  background: #ffffff;
  border: 1px solid #e1e5e9;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
</style>
