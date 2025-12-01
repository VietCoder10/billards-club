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
  model: {
    name: '',
    email: '',
    password: ''
  }
});
const props = defineProps(['data']);
onMounted(() => {
  if (props.data.isEdit) {
    state.model = props.data.user;
  }
});
const flagValidateUnique = ref(true);
defineRule('unique_custom', (value) => {
  return axios
    .post(route('admin.user.checkEmail'), {
      _token: Laravel.csrfToken,
      value: value,
      id: props.data.user?.id
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
      name: {
        required: 'ユーザー名を入力してください。',
        max: 'ユーザー名は255文字を超えてはなりません。'
      },
      email: {
        required: 'メールアドレスを入力してください。',
        max: 'メールアドレスは255文字を超えてはなりません。',
        unique_custom: 'このメールアドレスは既に登録されています。',
        email: 'メールアドレスを正確に入力してください。'
      },
      password: {
        required: 'パスワードを入力してください。',
        max: '半角英字、数字、記号を組み合わせて10文字以上16文字以内で入力してください。',
        min: '半角英字、数字、記号を組み合わせて10文字以上16文字以内で入力してください。',
        password_rule: '半角英字、数字、記号を組み合わせて10文字以上16文字以内で入力してください。'
      }
    }
  }
};
configure({
  generateMessage: localize(messError)
});
const onSubmit = () => {
  if (props.data.isEdit) {
    useForm(state.model).put(route('admin.user.update', props.data.user.id));
    return;
  }
  useForm(state.model).post(route('admin.user.store'));
};
</script>
<template>
  <AdminLayout>
    <template #content>
      <Panel :header="$page.props.data.title">
        <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
          <form @submit="handleSubmit($event, onSubmit)" class="form-data">
            <div class="form-group">
              <label class="form-label" require>ユーザー名: </label>
              <div class="form-input">
                <Field name="name" rules="required|max:255" v-model="state.model.name" v-slot="{ field, meta: metaField, handleChange }">
                  <InputText
                    class="w-full"
                    type="text"
                    v-model="state.model.name"
                    v-on:update:model-value="handleChange"
                    v-bind="field"
                    :class="{
                      'p-invalid': !metaField.valid && metaField.touched
                    }"
                  />
                  <ErrorMessage class="p-error" name="name" />
                </Field>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" require>メールアドレス: </label>
              <div class="form-input">
                <Field name="email" :rules="flagValidateUnique ? 'required|email|unique_custom|max:255' : 'required|email|max:255'" v-model="state.model.email" v-slot="{ field, meta: metaField, handleChange }">
                  <InputText
                    class="w-full"
                    @keypress="flagValidateUnique = false"
                    @blur="flagValidateUnique = true"
                    v-model="state.model.email"
                    v-bind="field"
                    autocomplete="username"
                    v-on:update:model-value="handleChange"
                    :class="{
                      'p-invalid': !metaField.valid && metaField.touched
                    }"
                  />
                  <ErrorMessage class="p-error" name="email" />
                </Field>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" v-if="props.data.isEdit">パスワード: </label>
              <label class="form-label" v-else require>パスワード: </label>
              <div class="form-input">
                <Field name="password" :rules="props.data.isEdit ? 'max:16|min:10|password_rule' : 'required|max:16|min:10|password_rule'" v-model="state.model.password" v-slot="{ field, meta: metaField, handleChange }">
                  <Password
                    v-bind="field"
                    v-model="state.model.password"
                    inputClass="w-full"
                    placeholder="パスワード"
                    hideIcon="pi pi-eye"
                    showIcon="pi pi-eye-slash"
                    :feedback="false"
                    aria-describedby="password-error"
                    :inputProps="{ autocomplete: 'new-password' }"
                    v-on:update:model-value="handleChange"
                    toggleMask
                    class="w-full"
                    :class="{
                      'p-invalid': !metaField.valid && metaField.touched
                    }"
                  />
                  <ErrorMessage class="p-error" name="password" />
                </Field>
              </div>
            </div>

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
