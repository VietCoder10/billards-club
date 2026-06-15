<script setup>
import { onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';

const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  }
});

const page = usePage();

const invoice = computed(() => props.data.invoice || {});
const invoiceDetails = computed(() => props.data.invoiceDetails || []);
const order = computed(() => props.data.order || {});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
};

const getPaymentMethodLabel = (method) => {
  return method == 2 ? 'Chuyển khoản' : 'Tiền mặt';
};

const qrCodeDescription = computed(() => {
  const orderNumber = invoice.value.invoice_number || '';
  return `Thanh toan don hang ${orderNumber}`.trim();
});

const qrCodeUrl = computed(() => {
  const bankId = page.props.value.vietqr?.bank_id || 'MB';
  const accountNo = page.props.value.vietqr?.account_no || '0356166166';
  const accountName = encodeURIComponent(page.props.value.vietqr?.account_name || 'BILLIARD CLUB');
  const template = page.props.value.vietqr?.template || 'qr_only';

  const amount = Math.round(invoice.value.final_amount || 0);
  const description = encodeURIComponent(qrCodeDescription.value);

  return `https://img.vietqr.io/image/${bankId}-${accountNo}-${template}.png?amount=${amount}&addInfo=${description}&accountName=${accountName}`;
});

onMounted(() => {
  setTimeout(() => {
    window.print();
  }, 800);
});

const handlePrint = () => {
  window.print();
};

const handleClose = () => {
  window.close();
};
</script>

<template>
  <div class="print-page-wrapper">
    <!-- Top Action Bar (hidden when printing) -->
    <div class="action-bar no-print">
      <div class="action-bar-content">
        <span class="action-title">Xem trước hóa đơn #{{ invoice.invoice_number }}</span>
        <div class="action-buttons">
          <button @click="handlePrint" class="btn btn-print">
            <i class="pi pi-print"></i> In hóa đơn (PDF)
          </button>
          <button @click="handleClose" class="btn btn-close">
            <i class="pi pi-times"></i> Đóng cửa sổ
          </button>
        </div>
      </div>
    </div>

    <!-- Printable Area -->
    <div class="print-container">
      <!-- Header -->
      <div class="header">
        <h1 class="brand-name">TQ BILLIARD CLUB</h1>
        <p class="brand-sub">Đường đi bóng đẳng cấp - Dịch vụ chuẩn 5 sao</p>
        <div class="brand-info">
          <p>Địa chỉ: Đường số 12, P. Linh Trung, TP. Thủ Đức, TP. HCM</p>
          <p>Hotline: 0356.166.166 - 0356.168.168</p>
        </div>
      </div>

      <div class="divider-double"></div>

      <!-- Title -->
      <h2 class="invoice-title">HÓA ĐƠN THANH TOÁN</h2>

      <!-- Metadata Grid -->
      <div class="metadata-grid">
        <div class="meta-row">
          <span class="meta-label">Số hóa đơn:</span>
          <span class="meta-value font-bold">#{{ invoice.invoice_number }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Bàn chơi:</span>
          <span class="meta-value font-bold text-uppercase">{{ invoice.table_name }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Thời gian vào:</span>
          <span class="meta-value">{{ formatDate(order.started_at) }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Thời gian ra:</span>
          <span class="meta-value">{{ formatDate(order.ended_at) }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Thời gian chơi:</span>
          <span class="meta-value">{{ order.total_minutes || 0 }} phút</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Thu ngân:</span>
          <span class="meta-value">{{ invoice.creator?.name || 'Hệ thống' }}</span>
        </div>
        <div v-if="invoice.customer" class="meta-row">
          <span class="meta-label">Khách hàng:</span>
          <span class="meta-value font-bold">{{ invoice.customer.name }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Ngày in:</span>
          <span class="meta-value">{{ formatDate(new Date()) }}</span>
        </div>
      </div>

      <div class="divider"></div>

      <!-- Items Table -->
      <table class="items-table">
        <thead>
          <tr>
            <th class="text-left">Tên mặt hàng / Dịch vụ</th>
            <th class="text-center">SL</th>
            <th class="text-right">Đơn giá</th>
            <th class="text-right">Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in invoiceDetails" :key="index">
            <td class="text-left font-medium">{{ item.item_name }}</td>
            <td class="text-center">{{ item.quantity }}</td>
            <td class="text-right">{{ formatCurrency(item.price) }}</td>
            <td class="text-right font-bold">{{ formatCurrency(item.sub_total) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="divider"></div>

      <!-- Summary Section -->
      <div class="summary-section">
        <div class="summary-row">
          <span>Tiền dịch vụ:</span>
          <span class="font-medium">{{ formatCurrency(invoice.service_total) }}</span>
        </div>
        <div class="summary-row">
          <span>Tiền bàn chơi:</span>
          <span class="font-medium">{{ formatCurrency(invoice.table_total) }}</span>
        </div>
        <div class="summary-row">
          <span>Tổng tiền trước giảm:</span>
          <span class="font-medium">{{ formatCurrency(invoice.total_amount) }}</span>
        </div>
        <div v-if="Number(invoice.discount) > 0" class="summary-row text-red">
          <span>Khuyến mãi HSSV (10% tiền bàn):</span>
          <span class="font-bold">-{{ formatCurrency(invoice.discount) }}</span>
        </div>
        <div class="divider-dashed"></div>
        <div class="summary-row total-row">
          <span class="total-label">TỔNG CỘNG THANH TOÁN:</span>
          <span class="total-amount">{{ formatCurrency(invoice.final_amount) }}</span>
        </div>
        <div class="summary-row mt-1 text-muted">
          <span>Phương thức thanh toán:</span>
          <span class="font-bold">{{ getPaymentMethodLabel(invoice.payment_method) }}</span>
        </div>
      </div>

      <!-- QR Code payment for transfers -->
      <div v-if="invoice.payment_method == 2" class="qr-payment-block">
        <div class="divider-dashed"></div>
        <p class="qr-title">QUÉT MÃ QR THANH TOÁN CHUYỂN KHOẢN</p>
        <div class="qr-content">
          <img :src="qrCodeUrl" alt="VietQR Pay" class="qr-image" />
          <div class="bank-details">
            <p>Ngân hàng: <strong>{{ $page.props.vietqr?.bank_id || 'MB' }}</strong></p>
            <p>Số tài khoản: <strong>{{ $page.props.vietqr?.account_no || '0356166166' }}</strong></p>
            <p>Chủ TK: <strong>{{ $page.props.vietqr?.account_name || 'BILLIARD CLUB' }}</strong></p>
            <p>Số tiền: <strong>{{ formatCurrency(invoice.final_amount) }}</strong></p>
            <p class="text-sm">Nội dung CK: <strong class="text-amber">{{ qrCodeDescription }}</strong></p>
          </div>
        </div>
      </div>

      <div class="divider-double"></div>

      <!-- Footer Message -->
      <div class="footer">
        <p class="thank-you">CẢM ƠN QUÝ KHÁCH & HẸN GẶP LẠI!</p>
        <p class="invoice-software">Hóa đơn được xuất tự động từ hệ thống TQ Billiard</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Reset and global styles for previewing */
.print-page-wrapper {
  font-family: 'Inter', 'Roboto', 'Segoe UI', sans-serif;
  color: #333;
  background-color: #f3f4f6;
  min-height: 100vh;
  padding: 80px 20px 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Action Bar */
.action-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 64px;
  background-color: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(229, 231, 235, 0.8);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 20px;
}
.action-bar-content {
  width: 100%;
  max-width: 580px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.action-title {
  font-weight: 700;
  color: #1e293b;
  font-size: 15px;
}
.action-buttons {
  display: flex;
  gap: 10px;
}
.btn {
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
  border: none;
}
.btn-print {
  background-color: #2563eb;
  color: #ffffff;
}
.btn-print:hover {
  background-color: #1d4ed8;
}
.btn-close {
  background-color: #e2e8f0;
  color: #475569;
}
.btn-close:hover {
  background-color: #cbd5e1;
}

/* Print container */
.print-container {
  width: 100%;
  max-width: 580px;
  background: #ffffff;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  box-sizing: border-box;
}

/* Header Brand */
.header {
  text-align: center;
}
.brand-name {
  font-size: 26px;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: 1px;
  margin: 0 0 5px;
}
.brand-sub {
  font-size: 12px;
  color: #64748b;
  font-style: italic;
  margin: 0 0 15px;
  font-weight: 500;
}
.brand-info {
  font-size: 12px;
  color: #475569;
  line-height: 1.6;
}
.brand-info p {
  margin: 2px 0;
}

/* Dividers */
.divider {
  border-top: 1px solid #e2e8f0;
  margin: 20px 0;
}
.divider-double {
  border-top: 3px double #cbd5e1;
  margin: 20px 0;
}
.divider-dashed {
  border-top: 1px dashed #cbd5e1;
  margin: 15px 0;
}

/* Invoice Title */
.invoice-title {
  text-align: center;
  font-size: 18px;
  font-weight: 850;
  color: #0f172a;
  letter-spacing: 1px;
  margin: 0 0 20px;
}

/* Metadata Grid */
.metadata-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px 20px;
  font-size: 13px;
  color: #334155;
  margin-bottom: 20px;
}
.meta-row {
  display: flex;
  justify-content: space-between;
}
.meta-label {
  color: #64748b;
}
.meta-value {
  color: #1e293b;
  text-align: right;
}

/* Items Table */
.items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  margin: 15px 0;
}
.items-table th {
  padding: 10px 5px;
  border-bottom: 2px solid #e2e8f0;
  color: #475569;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
}
.items-table td {
  padding: 12px 5px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
  vertical-align: middle;
}
.items-table tr:last-child td {
  border-bottom: none;
}

/* Alignment Helpers */
.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.font-medium { font-weight: 500; }
.font-bold { font-weight: 700; }
.text-uppercase { text-transform: uppercase; }
.text-red { color: #dc2626; }
.text-amber { color: #d97706; }
.mt-1 { margin-top: 8px; }
.text-muted { color: #64748b; }

/* Summary */
.summary-section {
  font-size: 13px;
  color: #334155;
}
.summary-row {
  display: flex;
  justify-content: space-between;
  margin: 6px 0;
}
.total-row {
  font-size: 16px;
  color: #0f172a;
  margin: 8px 0;
}
.total-label {
  font-weight: 800;
  letter-spacing: 0.5px;
}
.total-amount {
  font-weight: 900;
  color: #2563eb;
  font-size: 18px;
}

/* QR Code payment block */
.qr-payment-block {
  margin-top: 15px;
}
.qr-title {
  text-align: center;
  font-size: 11px;
  font-weight: 700;
  color: #475569;
  letter-spacing: 0.5px;
  margin: 5px 0 15px;
}
.qr-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 25px;
  background-color: #f8fafc;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}
.qr-image {
  width: 110px;
  height: 110px;
  background-color: #ffffff;
  padding: 6px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
}
.bank-details {
  font-size: 12px;
  color: #334155;
  line-height: 1.5;
}
.bank-details p {
  margin: 3px 0;
}

/* Footer Section */
.footer {
  text-align: center;
  margin-top: 20px;
}
.thank-you {
  font-size: 14px;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: 0.5px;
  margin: 0 0 5px;
}
.invoice-software {
  font-size: 10px;
  color: #94a3b8;
  margin: 0;
}

/* Media query for printing */
@media print {
  body {
    background-color: #ffffff !important;
  }
  .print-page-wrapper {
    background-color: #ffffff !important;
    padding: 0 !important;
    min-height: auto !important;
  }
  .no-print {
    display: none !important;
  }
  .print-container {
    width: 100% !important;
    max-width: 100% !important;
    border: none !important;
    box-shadow: none !important;
    padding: 10px 0 !important;
    margin: 0 !important;
  }
  .total-amount {
    color: #000000 !important;
  }
  .qr-content {
    background-color: transparent !important;
    border: none !important;
    padding: 10px 0 !important;
  }
}
</style>
