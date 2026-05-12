<script setup>
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { Field, ErrorMessage } from 'vee-validate';
import axios from 'axios';
import { useRequestStore } from '@/store/request';
import { Form as VeeForm } from 'vee-validate';
import CustomerAddModal from '@/Components/Billiard/CustomerAddModal.vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import FloatLabel from 'primevue/floatlabel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  initialSearchName: {
    type: String,
    default: ''
  },
  currentId: {
    type: Number,
    default: null
  }
});

const emit = defineEmits(['update:visible', 'select']);

const showAddModal = ref(false);

const handleOpenAddModal = () => {
  showAddModal.value = true;
};

const state = reactive({
  model: {},
  customers: [],
  loading: false,
  totalRecords: 0,
  lazyParams: {
    first: 0,
    page: 0,
    rows: 10,
    sortField: null,
    sortOrder: null
  }
});

const loadData = async () => {
  useRequestStore().showLoading();
  try {
    const params = {
      page: state.lazyParams.page + 1,
      limit_page: state.lazyParams.rows,
      sort: state.lazyParams.sortField,
      direction: state.lazyParams.sortOrder === 1 ? 'asc' : 'desc',
      ...state.model
    };

    const response = await axios.get(route('admin.customer.searchModal'), { params });
    state.customers = response.data.data;
    state.totalRecords = response.data.total;
  } catch (error) {
    console.error('Error fetching customers:', error);
  } finally {
    useRequestStore().hideLoading();
  }
};

const onPage = (event) => {
  state.lazyParams = event;
  loadData();
};

const onSort = (event) => {
  state.lazyParams = event;
  loadData();
};

const handleSearch = () => {
  state.lazyParams.page = 0; // Reset to first page on search
  state.lazyParams.first = 0;
  loadData();
};

const clearForm = () => {
  state.model = {};
  handleSearch();
};

const selectCustomer = (customer) => {
  const normalized = { ...customer, id: customer.id ?? customer.value };
  emit('select', normalized);
  emit('update:visible', false);
};

watch(
  () => props.visible,
  (newValue) => {
    if (newValue) {
      if (props.initialSearchName) {
        state.model.search_name = props.initialSearchName;
        handleSearch();
      } else {
        loadData();
      }
    }
  }
);

const highlightRow = (row) => {
  if (row.id === props.currentId) {
    return '!bg-[#F5E1E1]';
  }
  return '';
};
</script>

<template>
  <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal header="顧客検索" :dismissableMask="true" :style="{ width: '80vw' }" :breakpoints="{ '960px': '90vw', '640px': '95vw' }">
    <VeeForm as="div" v-slot="{ handleSubmit }">
      <form @submit="handleSubmit($event, loadData)" id="customer-search-form" class="form-data">
        <div class="flex flex-col gap-3 mt-4">
          <div class="flex flex-wrap gap-3">
            <div class="flex flex-col">
              <Field name="search_name" v-model="state.model.search_name" v-slot="{ field, meta: metaField, handleChange }">
                <FloatLabel variant="on">
                  <InputText class="w-full" :modelValue="field.value" @update:modelValue="handleChange" v-bind="field" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                  <label :class="field.name">名前</label>
                </FloatLabel>
                <ErrorMessage class="p-error" :name="field.name" />
              </Field>
            </div>
            <div class="flex flex-col">
              <Field name="tel" v-model="state.model.tel" v-slot="{ field, meta: metaField, handleChange }">
                <FloatLabel variant="on">
                  <InputText class="w-full" :modelValue="field.value" @update:modelValue="handleChange" v-bind="field" :class="{ 'p-invalid': !metaField.valid && metaField.touched }" />
                  <label>電話番号</label>
                </FloatLabel>
                <ErrorMessage class="p-error" :name="field.name" />
              </Field>
            </div>
          </div>
        </div>
        <div class="flex justify-center items-center m-4">
          <Button class="ml-2" @click="clearForm" severity="secondary"> <i class="pi pi-filter-slash"></i> &nbsp; 条件クリア </Button>
          <Button class="ml-2" type="submit"> <i class="pi pi-search"></i> &nbsp; 検索 </Button>
          <Button class="ml-2" label="新規登録" @click="handleOpenAddModal" icon="pi pi-plus" />
        </div>
      </form>
    </VeeForm>

    <div class="card">
      <DataTable
        :value="state.customers"
        v-if="state.customers.length"
        :lazy="true"
        :paginator="true"
        :rows="10"
        ref="dt"
        :totalRecords="state.totalRecords"
        @page="onPage"
        @sort="onSort"
        dataKey="id"
        :first="state.lazyParams.first"
        tableStyle="min-width: 50rem"
        selectionMode="single"
        @row-select="selectCustomer($event.data)"
        :rowClass="highlightRow"
      >
        <ColumnGroup type="header">
          <Row>
            <Column field="id" header="ID" sortable />
            <Column field="name" header="氏名" sortable />
            <Column field="email" header="メールアドレス" sortable />
            <Column field="phone" header="電話番号" sortable />
            <Column class="w-[120px]" header="" />
          </Row>
        </ColumnGroup>
        <Column field="id" />
        <Column field="name" />
        <Column field="email" />
        <Column field="phone" />
        <Column>
          <template #body="{ data }">
            <Button @click="selectCustomer(data)"> <i class="pi pi-save"></i> &nbsp; 選択 </Button>
          </template>
        </Column>
      </DataTable>
      <div v-else class="text-center p-4">データが見つかりません。</div>
    </div>
  </Dialog>
  <CustomerAddModal v-model:visible="showAddModal" @saved="clearForm" />
</template>
