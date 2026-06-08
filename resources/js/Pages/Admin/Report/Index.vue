<script setup>
import { ref, onMounted, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import Chart from 'primevue/chart';
import Dropdown from 'primevue/dropdown';
import axios from 'axios';

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
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
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

// AI Report logic
const activeTab = ref('stats');
const aiTimeRange = ref('this_month'); // today, this_week, this_month, custom
const customStartDate = ref('');
const customEndDate = ref('');

const aiLoading = ref(false);
const aiLoadingProgress = ref(0);
const aiLoadingText = ref('');
const aiReportRaw = ref('');
const aiReportSections = ref(null);
const aiMetrics = ref(null);
const aiError = ref('');

const progressInterval = ref(null);

const loadingTexts = [
  'Đang kết nối cơ sở dữ liệu để thu thập thông tin...',
  'Đang phân tích và tính toán doanh thu tiền bàn, doanh thu dịch vụ...',
  'Đang thống kê và tính toán hiệu suất hoạt động của toàn bộ bàn chơi...',
  'Đang phân tích hành vi tiêu dùng và xác định nhóm khách hàng chi tiêu nhiều nhất...',
  'Đang đóng gói dữ liệu và gửi yêu cầu phân tích tới hệ thống trí tuệ nhân tạo Gemini...',
  'Gemini đang phân tích số liệu và xây dựng báo cáo quản trị...',
  'Trợ lý AI đang đề xuất các giải pháp tối ưu hóa doanh thu và vận hành quán...',
  'Đang hoàn tất việc xây dựng báo cáo và chuẩn bị hiển thị...'
];

const startAiReportGeneration = async () => {
  aiLoading.value = true;
  aiError.value = '';
  aiReportRaw.value = '';
  aiReportSections.value = null;
  aiMetrics.value = null;
  aiLoadingProgress.value = 5;
  aiLoadingText.value = loadingTexts[0];

  let textIdx = 0;
  progressInterval.value = setInterval(() => {
    if (aiLoadingProgress.value < 90) {
      aiLoadingProgress.value += Math.floor(Math.random() * 8) + 3;
    }
    textIdx = (textIdx + 1) % loadingTexts.length;
    aiLoadingText.value = loadingTexts[textIdx];
  }, 2500);

  try {
    const payload = {
      time_range: aiTimeRange.value,
    };
    if (aiTimeRange.value === 'custom') {
      payload.start_date = customStartDate.value;
      payload.end_date = customEndDate.value;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await axios.post(route('admin.report.aiReport'), payload, {
      headers: { 'X-CSRF-TOKEN': csrfToken },
      timeout: 90000, // 90s để AI có đủ thời gian phản hồi
    });
    
    clearInterval(progressInterval.value);
    aiLoadingProgress.value = 100;
    aiLoadingText.value = 'Đã hoàn tất báo cáo!';
    
    setTimeout(() => {
      aiLoading.value = false;
      aiReportRaw.value = response.data.report;
      aiMetrics.value = response.data.metrics;
      aiReportSections.value = parseAIReport(response.data.report);
    }, 800);

  } catch (error) {
    clearInterval(progressInterval.value);
    aiLoading.value = false;
    aiError.value = error.response?.data?.error || 'Đã xảy ra lỗi không xác định khi kết nối với máy chủ AI.';
  }
};

const parseAIReport = (text) => {
  if (!text) return null;
  
  const sections = [
    { title: '1. Tổng quan hoạt động', icon: 'pi pi-chart-line', key: 'tong_quan', content: '', color: 'border-l-blue-500 text-blue-600 bg-blue-50/50' },
    { title: '2. Phân tích doanh thu', icon: 'pi pi-money-bill', key: 'doanh_thu', content: '', color: 'border-l-emerald-500 text-emerald-600 bg-emerald-50/50' },
    { title: '3. Phân tích khách hàng', icon: 'pi pi-users', key: 'khach_hang', content: '', color: 'border-l-purple-500 text-purple-600 bg-purple-50/50' },
    { title: '4. Phân tích hiệu suất sử dụng bàn', icon: 'pi pi-table', key: 'hieu_suat_ban', content: '', color: 'border-l-orange-500 text-orange-600 bg-orange-50/50' },
    { title: '5. Điểm mạnh nổi bật', icon: 'pi pi-thumbs-up', key: 'diem_manh', content: '', color: 'border-l-teal-500 text-teal-600 bg-teal-50/50' },
    { title: '6. Các vấn đề cần cải thiện', icon: 'pi pi-exclamation-triangle', key: 'can_cai_thien', content: '', color: 'border-l-rose-500 text-rose-600 bg-rose-50/50' },
    { title: '7. Đề xuất cải thiện', icon: 'pi pi-lightbulb', key: 'de_xuat', content: '', color: 'border-l-indigo-500 text-indigo-600 bg-indigo-50/50' },
    { title: '8. Kết luận', icon: 'pi pi-info-circle', key: 'ket_luan', content: '', color: 'border-l-zinc-500 text-zinc-650 bg-zinc-50/50' },
  ];

  const lines = text.split('\n');
  let currentSectionIndex = -1;

  for (let line of lines) {
    const cleanedLine = line.replace(/\*\*/g, '').trim();
    const match = cleanedLine.match(/^\s*(\d+)\.\s*(.+)$/);
    
    if (match) {
      const num = parseInt(match[1]);
      if (num >= 1 && num <= 8) {
        currentSectionIndex = num - 1;
        continue;
      }
    }

    if (currentSectionIndex >= 0 && currentSectionIndex < 8) {
      sections[currentSectionIndex].content += line + '\n';
    }
  }

  sections.forEach(s => {
    s.content = s.content.trim();
  });

  if (sections.every(s => !s.content)) {
    return null;
  }

  return sections;
};

const formatSectionContent = (content) => {
  if (!content) return [];
  const paragraphs = content.split('\n');
  const result = [];
  let id = 0;
  
  for (let p of paragraphs) {
    const trimmed = p.trim();
    if (!trimmed) continue;
    
    if (trimmed.startsWith('-') || trimmed.startsWith('*') || trimmed.startsWith('•') || /^\d+\.\s/.test(trimmed)) {
      let cleanText = trimmed;
      if (trimmed.startsWith('-') || trimmed.startsWith('*') || trimmed.startsWith('•')) {
        cleanText = trimmed.substring(1).trim();
      } else {
        cleanText = trimmed.replace(/^\d+\.\s*/, '');
      }
      const text = cleanText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      result.push({ id: id++, type: 'list', text });
    } else {
      const text = trimmed.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      result.push({ id: id++, type: 'paragraph', text });
    }
  }
  return result;
};
</script>

<template>
  <AdminLayout>
    <template #content>
      <div class="card p-4">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
          <h3 class="font-black text-2xl text-gray-800 m-0">Báo cáo & Phân tích</h3>
          <div v-if="activeTab === 'stats'" class="flex gap-2">
            <Select v-model="selectedMonth" :options="months" optionLabel="name" optionValue="value" @change="onFilterChange" class="w-10rem" />
            <Select v-model="selectedYear" :options="years" optionLabel="name" optionValue="value" @change="onFilterChange" class="w-10rem" />
          </div>
        </div>

        <!-- Tab List Header -->
        <div class="mb-5 border-b border-gray-200">
          <Tabs v-model:value="activeTab">
            <TabList class="border-b-0">
              <Tab class="custom-tab" value="stats">
                <div class="flex items-center gap-2 px-4 py-2.5 font-bold text-xs uppercase tracking-wider">
                  <i class="pi pi-chart-bar text-sm"></i>
                  Biểu đồ & Thống kê
                </div>
              </Tab>
              <Tab class="custom-tab" value="ai_report">
                <div class="flex items-center gap-2 px-4 py-2.5 font-bold text-xs uppercase tracking-wider">
                  <i class="pi pi-sparkles text-sm text-blue-500 animate-pulse"></i>
                  Trợ lý báo cáo AI
                </div>
              </Tab>
            </TabList>
          </Tabs>
        </div>

        <!-- Tab Content 1: General Stats & Bar Chart -->
        <div v-if="activeTab === 'stats'">
          <div class="summary-grid">
            <div class="summary-card border-l-4 border-l-green-500">
              <div class="title">Hôm nay</div>
              <div class="value text-green-600">{{ formatCurrency(data.revenueToday) }}</div>
            </div>
            <div class="summary-card border-l-4 border-l-blue-500">
              <div class="title">Trong tháng {{ data.selectedMonth }}/{{ data.selectedYear }}</div>
              <div class="value text-blue-600">{{ formatCurrency(data.revenueThisMonth) }}</div>
            </div>
            <div class="summary-card border-l-4 border-l-purple-500">
              <div class="title">Trong năm {{ data.selectedYear }}</div>
              <div class="value text-purple-600">{{ formatCurrency(data.revenueThisYear) }}</div>
            </div>
          </div>

          <div class="chart-container">
            <h5 class="text-gray-700 font-bold mb-4">Biểu đồ doanh thu tháng {{ data.selectedMonth }}/{{ data.selectedYear }}</h5>
            <Chart type="bar" :data="chartData" :options="chartOptions" style="height: 400px" />
          </div>
        </div>

        <!-- Tab Content 2: AI Report Generator -->
        <div v-else-if="activeTab === 'ai_report'">
          <!-- AI Hero Card -->
          <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-900 text-white shadow-md mb-6 relative overflow-hidden">
            <div class="relative z-10 max-w-2xl">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black tracking-wider bg-white/20 text-white uppercase mb-3">
                <i class="pi pi-sparkles"></i> AI Powered Analyst
              </span>
              <h4 class="text-2xl font-black mb-2 tracking-tight">Trợ lý Phân tích Kinh doanh AI</h4>
              <p class="text-sm opacity-90 leading-relaxed font-medium">
                Tự động tổng hợp số liệu thực tế từ cơ sở dữ liệu của quán (doanh thu tiền bàn, dịch vụ, hiệu suất bàn, hành vi khách hàng) và gửi đến AI để tự động tạo báo cáo quản trị chuyên nghiệp kèm theo đề xuất giải pháp tối ưu vận hành.
              </p>
            </div>
            <div class="absolute right-6 bottom-0 top-0 items-center justify-center opacity-10 pointer-events-none hidden md:flex">
              <i class="pi pi-sparkles text-[180px]"></i>
            </div>
          </div>

          <!-- Configuration Card -->
          <div class="p-5 border border-zinc-150 rounded-2xl bg-white shadow-sm mb-6">
            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
              <i class="pi pi-cog text-blue-500"></i> Cấu hình kỳ báo cáo
            </h5>
            
            <div class="flex flex-wrap items-end gap-5">
              <!-- Period Selector -->
              <div class="flex flex-col gap-1.5">
                <span class="text-xs font-bold text-gray-500">Khoảng thời gian</span>
                <div class="flex gap-2">
                  <button 
                    v-for="opt in [
                      { label: 'Hôm nay', val: 'today' },
                      { label: 'Tuần này', val: 'this_week' },
                      { label: 'Tháng này', val: 'this_month' },
                      { label: 'Tùy chỉnh', val: 'custom' }
                    ]"
                    :key="opt.val"
                    @click="aiTimeRange = opt.val"
                    type="button"
                    :class="[
                      'px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200',
                      aiTimeRange === opt.val
                        ? 'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-500/10'
                        : 'bg-white border-zinc-200 hover:border-zinc-350 text-gray-650 hover:bg-zinc-50'
                    ]"
                  >
                    {{ opt.label }}
                  </button>
                </div>
              </div>

              <!-- Custom Dates -->
              <div v-if="aiTimeRange === 'custom'" class="flex items-center gap-3 animate-fade-in">
                <div class="flex flex-col gap-1.5">
                  <span class="text-xs font-bold text-gray-500">Từ ngày</span>
                  <input type="date" v-model="customStartDate" class="p-inputtext text-xs font-bold py-2 px-3 rounded-xl border border-zinc-200 focus:border-blue-500 outline-none w-[150px]" />
                </div>
                <div class="flex flex-col gap-1.5">
                  <span class="text-xs font-bold text-gray-500">Đến ngày</span>
                  <input type="date" v-model="customEndDate" class="p-inputtext text-xs font-bold py-2 px-3 rounded-xl border border-zinc-200 focus:border-blue-500 outline-none w-[150px]" />
                </div>
              </div>

              <!-- Generate Button -->
              <Button 
                label="Tạo báo cáo AI" 
                icon="pi pi-sparkles" 
                class="p-button-primary rounded-xl font-bold text-xs py-2.5 px-5 ml-auto w-full md:w-auto shadow-lg shadow-blue-500/15" 
                :loading="aiLoading"
                @click="startAiReportGeneration"
              />
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="aiLoading" class="p-8 border border-zinc-150 rounded-2xl bg-white shadow-sm flex flex-col items-center justify-center text-center my-10 max-w-2xl mx-auto animate-pulse">
            <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-4 text-blue-600">
              <i class="pi pi-spin pi-spinner text-2xl"></i>
            </div>
            <h4 class="font-extrabold text-gray-800 text-lg mb-2">Trợ lý AI đang làm việc...</h4>
            <p class="text-xs text-gray-500 mb-6 max-w-md">{{ aiLoadingText }}</p>
            
            <div class="w-full bg-gray-100 rounded-full h-2.5 max-w-md overflow-hidden relative border">
              <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" :style="{ width: aiLoadingProgress + '%' }"></div>
            </div>
            <span class="text-[10px] font-black text-blue-600 mt-2">{{ aiLoadingProgress }}%</span>
          </div>

          <!-- Error State -->
          <div v-if="aiError" class="p-6 border border-rose-200 rounded-2xl bg-rose-50 text-rose-700 shadow-sm my-6 max-w-2xl mx-auto flex flex-col gap-3 items-center text-center">
            <i class="pi pi-exclamation-triangle text-3xl text-rose-500"></i>
            <h4 class="font-black text-base">Không thể tạo báo cáo</h4>
            <p class="text-xs leading-relaxed max-w-md">{{ aiError }}</p>
            <button @click="aiError = ''" class="mt-2 px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-750 transition-colors shadow">
              Thử lại
            </button>
          </div>

          <!-- Report Data Content -->
          <div v-if="aiReportSections && !aiLoading" class="animate-fade-in">
            <!-- Brief quantitative summary board -->
            <div v-if="aiMetrics" class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
              <div class="p-4 bg-white border border-zinc-150 rounded-xl text-center shadow-sm">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Tổng doanh thu</span>
                <span class="font-black text-sm text-green-600 block">{{ formatCurrency(aiMetrics.total_revenue) }}</span>
              </div>
              <div class="p-4 bg-white border border-zinc-150 rounded-xl text-center shadow-sm">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Tiền giờ bàn</span>
                <span class="font-black text-sm text-blue-600 block">{{ formatCurrency(aiMetrics.table_revenue) }}</span>
              </div>
              <div class="p-4 bg-white border border-zinc-150 rounded-xl text-center shadow-sm">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Tiền dịch vụ</span>
                <span class="font-black text-sm text-purple-600 block">{{ formatCurrency(aiMetrics.service_revenue) }}</span>
              </div>
              <div class="p-4 bg-white border border-zinc-150 rounded-xl text-center shadow-sm">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Số đơn hàng</span>
                <span class="font-black text-sm text-gray-800 block">{{ aiMetrics.order_count }} đơn</span>
              </div>
              <div class="p-4 bg-white border border-zinc-150 rounded-xl text-center shadow-sm">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Tổng giờ chơi</span>
                <span class="font-black text-sm text-orange-600 block">{{ aiMetrics.table_hours }} giờ</span>
              </div>
              <div class="p-4 bg-white border border-zinc-150 rounded-xl text-center shadow-sm">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Hiệu suất bàn</span>
                <span class="font-black text-sm text-cyan-600 block">{{ aiMetrics.usage_rate }}%</span>
              </div>
            </div>

            <!-- Detailed Grid of AI sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div 
                v-for="section in aiReportSections" 
                :key="section.key" 
                :class="[
                  'p-6 border border-zinc-150 rounded-2xl bg-white shadow-sm flex flex-col gap-4 border-l-4 transition-all duration-300 hover:shadow-md',
                  section.key === 'tong_quan' || section.key === 'de_xuat' || section.key === 'ket_luan'
                    ? 'col-span-1 md:col-span-2'
                    : 'col-span-1'
                ]"
              >
                <!-- Section Header -->
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-100">
                  <div :class="['w-9 h-9 rounded-xl flex items-center justify-center text-lg', section.color]">
                    <i :class="section.icon"></i>
                  </div>
                  <h4 class="font-extrabold text-gray-800 text-base">{{ section.title }}</h4>
                </div>
                
                <!-- Section Content List/Paragraphs -->
                <div class="text-sm text-gray-650 flex flex-col gap-3">
                  <div v-for="part in formatSectionContent(section.content)" :key="part.id">
                    <!-- If bullet point list item -->
                    <div v-if="part.type === 'list'" class="flex items-start gap-2.5 pl-1.5">
                      <i class="pi pi-check text-green-500 mt-1 flex-shrink-0 text-xs"></i>
                      <span class="text-gray-700 text-sm leading-relaxed" v-html="part.text"></span>
                    </div>
                    <!-- If normal paragraph text -->
                    <p v-else class="text-gray-700 text-sm leading-relaxed" v-html="part.text"></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Disclaimer alert -->
            <div class="mt-6 p-4 rounded-xl border border-zinc-200 bg-zinc-50 flex items-center gap-3 text-xs text-gray-500">
              <i class="pi pi-info-circle text-gray-400 text-base"></i>
              <span>Báo cáo này được tạo tự động bởi trí tuệ nhân tạo Gemini dựa trên số liệu kinh doanh thực tế lưu trữ trong hệ thống tại thời điểm phân tích.</span>
            </div>
          </div>
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

:deep(.custom-tab) {
  padding: 0 !important;
}
:deep(.p-tab) {
  border-bottom: 2px solid transparent;
  transition: all 0.3s;
}
:deep(.p-tab-active) {
  color: #3b82f6 !important;
  border-bottom-color: #3b82f6 !important;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}
</style>
