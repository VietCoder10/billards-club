<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useRequestStore } from '@/store/request';
import { ref, onMounted, reactive } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
import axios from 'axios';
const state = reactive({
  model: {}
});
const tabs = ref([
  { title: '基本情報', value: '0' },
  { title: '共通設備', value: '1' },
  { title: '間取情報', value: '2' },
  { title: '用途情報', value: '3' },
  { title: '問合せ', value: '4' },
  { title: '物件画像', value: '5' },
  { title: '追加画像', value: '6' },
  { title: '過去画像', value: '7' },
  { title: '定期清掃', value: '8' }
]);
const props = defineProps(['data']);
onMounted(() => {
  if (props.data.isEdit) {
    state.model = props.data.building;
  }
});
const flagValidateUnique = ref(true);
defineRule('unique_code', (value) => {
  if (value == '') {
    return true;
  }
  return axios
    .post(route('admin.building.checkCode'), {
      _token: Laravel.csrfToken,
      value: value,
      id: props.data.building?.id
    })
    .then(function (response) {
      return response.data.valid;
    })
    .catch((error) => {});
});
const onInvalidSubmit = ({ errors }) => {
  let firstInputError = Object.entries(errors)[0][0];
  let ele = $('[name="' + firstInputError + '"]');
  if ($('[name="' + firstInputError + '"]').hasClass('hidden') || $('[name="' + firstInputError + '"]').attr('type') == 'hidden') {
    ele = $('[name="' + firstInputError + '"]').closest('div');
  }
  ele.focus();
  $('html, body').animate(
    {
      scrollTop: ele.offset().top - 150 + 'px'
    },
    500
  );
};
let messError = {
  en: {
    fields: {
      building_name: {
        required: '建物名を入力してください。',
        max: '建物名は255文字を超えてはなりません。'
      },
      building_name_kana: {
        required: 'ふりがなを入力してください。',
        kata: 'ふりがなはカタカナで入力してください。',
        max: 'ふりがなは255文字を超えてはなりません。'
      },
      building_code: {
        code_rule: '建物コードは半角英数字で入力してください。',
        unique_code: 'この建物コードは既に登録されています。',
        max: '建物コードは255文字を超えてはなりません。'
      },
      person_in_charge: {
        max: '物件担当は255文字を超えてはなりません。'
      },
      construction_reason: {
        max: '管理担当は255文字を超えてはなりません。'
      },
      building_short_name: {
        max: '建物名（省略可）は255文字を超えてはなりません。'
      }
    }
  }
};
configure({
  generateMessage: localize(messError)
});
const onSubmit = () => {
  if (props.data.isEdit) {
    useForm(state.model).put(route('admin.ledger.update', props.data.building.id));
    return;
  }
  useForm(state.model).post(route('admin.ledger.store'));
};
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" class="form-data">
            <Tabs value="0">
              <TabList>
                <Tab v-for="tab in tabs" :key="tab.title" :value="tab.value">{{ tab.title }}</Tab>
              </TabList>
              <TabPanels>
                <TabPanel value="0">
                  <div class="card flex flex-col gap-4">
                    <div class="flex flex-wrap gap-4">
                      <div class="flex flex-col grow basis-0 gap-2">
                        <Field name="building_name" rules="required|max:255" v-model="state.model.building_name" v-slot="{ field, meta: metaField, handleChange }">
                          <FloatLabel variant="on">
                            <InputText
                              class="w-full"
                              type="text"
                              v-model="state.model.building_name"
                              v-on:update:model-value="handleChange"
                              v-bind="field"
                              :class="{
                                'p-invalid': !metaField.valid && metaField.touched
                              }"
                            />
                            <label for="building_name">建物名 <span class="required">(必須)</span></label>
                          </FloatLabel>
                          <ErrorMessage class="p-error" name="building_name" />
                        </Field>
                      </div>
                      <div class="flex flex-col grow basis-0 gap-2">
                        <Field name="building_name_kana" rules="required|kata|max:255" v-model="state.model.building_name_kana" v-slot="{ field, meta: metaField, handleChange }">
                          <FloatLabel variant="on">
                            <InputText
                              class="w-full"
                              type="text"
                              v-model="state.model.building_name_kana"
                              v-on:update:model-value="handleChange"
                              v-bind="field"
                              :class="{
                                'p-invalid': !metaField.valid && metaField.touched
                              }"
                            />
                            <label for="building_name_kana">ふりがな <span class="required">(必須)</span></label>
                          </FloatLabel>
                          <ErrorMessage class="p-error" name="building_name_kana" />
                        </Field>
                      </div>
                    </div>

                    <div class="flex flex-wrap gap-4">
                      <div class="flex flex-col grow basis-0 gap-2">
                        <Field name="building_short_name" rules="max:255" v-model="state.model.building_short_name" v-slot="{ field, meta: metaField, handleChange }">
                          <FloatLabel variant="on">
                            <InputText
                              class="w-full"
                              type="text"
                              v-model="state.model.building_short_name"
                              v-on:update:model-value="handleChange"
                              v-bind="field"
                              :class="{
                                'p-invalid': !metaField.valid && metaField.touched
                              }"
                            />
                            <label for="building_short_name">建物名（省略可）</label>
                          </FloatLabel>
                          <ErrorMessage class="p-error" name="building_short_name" />
                        </Field>
                      </div>
                      <div class="flex flex-col grow basis-0 gap-2">
                        <Field name="building_code" :rules="flagValidateUnique ? 'code_rule|unique_code|max:255' : 'code_rule|max:255'" v-model="state.model.building_code" v-slot="{ field, meta: metaField, handleChange }">
                          <FloatLabel variant="on">
                            <InputText
                              class="w-full"
                              type="text"
                              @keypress="flagValidateUnique = false"
                              @blur="flagValidateUnique = true"
                              v-model="state.model.building_code"
                              v-on:update:model-value="handleChange"
                              v-bind="field"
                              :class="{
                                'p-invalid': !metaField.valid && metaField.touched
                              }"
                            />
                            <label for="building_code">建物コード</label>
                          </FloatLabel>
                          <ErrorMessage class="p-error" name="building_code" />
                        </Field>
                      </div>
                    </div>
                    <div class="flex flex-wrap gap-4">
                      <div class="flex flex-col grow basis-0 gap-2">
                        <Field name="person_in_charge" rules="max:255" v-model="state.model.person_in_charge" v-slot="{ field, meta: metaField, handleChange }">
                          <FloatLabel variant="on">
                            <InputText
                              class="w-full"
                              type="text"
                              v-model="state.model.person_in_charge"
                              v-on:update:model-value="handleChange"
                              v-bind="field"
                              :class="{
                                'p-invalid': !metaField.valid && metaField.touched
                              }"
                            />
                            <label for="person_in_charge">物件担当</label>
                          </FloatLabel>
                          <ErrorMessage class="p-error" name="person_in_charge" />
                        </Field>
                      </div>
                      <div class="flex flex-col grow basis-0 gap-2">
                        <Field name="construction_reason" rules="max:255" v-model="state.model.construction_reason" v-slot="{ field, meta: metaField, handleChange }">
                          <FloatLabel variant="on">
                            <InputText
                              class="w-full"
                              type="text"
                              v-model="state.model.construction_reason"
                              v-on:update:model-value="handleChange"
                              v-bind="field"
                              :class="{
                                'p-invalid': !metaField.valid && metaField.touched
                              }"
                            />
                            <label for="construction_reason">管理担当</label>
                          </FloatLabel>
                          <ErrorMessage class="p-error" name="construction_reason" />
                        </Field>
                      </div>
                    </div>
                  </div>
                </TabPanel>
              </TabPanels>
            </Tabs>
            <div class="form-action">
              <Link :href="$page.props.data.urlBack">
                <Button label="キャンセル " icon="pi pi-arrow-left" class="btn-action"></Button>
              </Link>
              <Button label="登録" type="submit" icon="pi pi-save" class="btn-action"></Button>
            </div>
          </form>
        </VeeForm>
      </Panel>
    </template>
  </AdminLayout>
</template>
<style scoped>
</style>