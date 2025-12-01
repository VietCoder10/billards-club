<template>
  <div class="">
    <DataTable :value="[]" class="hidden" tableStyle="min-width: 50rem">
      <Column field="code" header="Code"></Column>
      <Column field="name" header="Name"></Column>
      <Column field="category" header="Category"></Column>
      <Column field="quantity" header="Quantity"></Column>
    </DataTable>
    <span class="left">表示件数</span>
    <Select v-model="limit" :options="limitPageOption" class="w-120 ml-2">
      <template #value="slotProps">
        <span>
          {{ slotProps.value + '件' }}
        </span>
      </template>
    </Select>
    <!-- <Dropdown
      id="pageSize"
      class="form-select page-size-select cursor-point"
      name="limit"
      @change="onChange($event)"
    >
      <option
        v-for="value in limitPageOption"
        :key="'option_' + value"
        :value="value"
        v-bind:selected="value == newSizeLimit"
      >
        {{ value + '件' }}
      </option>
    </Dropdown> -->
  </div>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3';
export default {
  data() {
    return {
      model: useForm({}),
      limit: this.$page.props.data.request.limit_page ? parseInt(this.$page.props.data.request.limit_page) : 20,
      limitPageOption: [
        20, 50, 100
        // { label: '20件', value: 20 },
        // { label: '50件', value: 50 },
        // { label: '100件', value: 100 },
      ]
    };
  },
  watch: {
    limit(val) {
      let pathname = window.location.pathname;
      let search = window.location.search;
      if (search.indexOf('limit_page=') >= 0) {
        search = search.replace(/limit_page=([0-9]*)/gi, 'limit_page=' + val);
      } else {
        if (search == '') {
          search = search + '?limit_page=' + val;
        } else {
          search = search + '&limit_page=' + val;
        }
      }
      this.model.get(window.location.origin + pathname + search);
    }
  },
  methods: {
    onChange(event) {
      //   let pathname = window.location.pathname
      //   let search = window.location.search
      //   if (search.indexOf('limit_page=') >= 0) {
      //     search = search.replace(
      //       /limit_page=([0-9]*)/gi,
      //       'limit_page=' + event.target.value
      //     )
      //   } else {
      //     if (search == '') {
      //       search = search + '?limit_page=' + event.target.value
      //     } else {
      //       search = search + '&limit_page=' + event.target.value
      //     }
      //   }
      //   this.model.get(window.location.origin + pathname + search)
    }
  }
};
</script>
