<script setup>
import logo from '@/assets/imgs/logo.svg';
import GuestLayout from '@/Layouts/Admin/GuestLayout.vue';

import { useForm, Link } from '@inertiajs/inertia-vue3';
import { ref, reactive, onMounted, watch } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
const state = reactive({
  model: {}
});
const props = defineProps(['data']);
const onSubmit = () => {
  useForm(state.model).put(route('admin.reset-password.update', props.data.token));
};
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
onMounted(() => {});
watch(
  () => props.data,
  () => {
  }
);
let messError = {
  en: {
    fields: {
      password: {
        required: 'Please enter a value.',
        max: 'Please enter 100 characters or fewer.',
        min: 'Please enter at least 8 characters.',
        password_str: 'The password must include both uppercase and lowercase letters.',
        password_number: 'The password must include at least one number.'
      },
      password_confirmation: {
        required: 'Please enter a value.',
        confirmed: 'The password confirmation does not match.'
      }
    }
  }
};
configure({
  generateMessage: localize(messError)
});
</script>

<template>
  <GuestLayout>
    <template #content>
      <VeeForm as="div" v-slot="{ handleSubmit }" @invalid-submit="onInvalidSubmit">
        <form @submit="handleSubmit($event, onSubmit)">
          <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
            <div class="flex flex-col items-center justify-center">
              <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                  <div class="text-center mb-8">
                    <img :src="logo" class="logo" />
                  </div>
                  <div class="mb-4">
                    <label for="password" class="block text-surface-900 dark:text-surface-0 mb-1">Password</label>
                    <Field name="password" rules="required|max:100|min:8|password_str|password_number" v-model="state.model.password" v-slot="{ field, meta: metaField, handleChange }">
                      <Password
                        v-bind="field"
                        v-model="state.model.password"
                        inputClass="w-full"
                        :class="{
                          'p-invalid': !metaField.valid && metaField.touched
                        }"
                        placeholder="Password"
                        hideIcon="pi pi-eye"
                        name="password"
                        id="password"
                        showIcon="pi pi-eye-slash"
                        :feedback="false"
                        aria-describedby="password-error"
                        :inputProps="{ autocomplete: 'off' }"
                        v-on:update:model-value="handleChange"
                        toggleMask
                        fluid
                        class="w-full md:w-[30rem]"
                      />
                      <ErrorMessage class="p-error" name="password" />
                    </Field>
                  </div>
                  <div class="mb-4">
                    <label for="password_confirmation" class="block text-surface-900 dark:text-surface-0 mb-1">Password (Confirmation)</label>
                    <Field name="password_confirmation" rules="required|confirmed:@password" v-model="state.model.password_confirmation" v-slot="{ field, meta: metaField, handleChange }">
                      <Password
                        v-bind="field"
                        v-model="state.model.password_confirmation"
                        inputClass="w-full"
                        :class="{
                          'p-invalid': !metaField.valid && metaField.touched
                        }"
                        placeholder="  Password (Confirmation)"
                        hideIcon="pi pi-eye"
                        showIcon="pi pi-eye-slash"
                        :feedback="false"
                        aria-describedby="password-confirmation-error"
                        :inputProps="{ autocomplete: 'off' }"
                        v-on:update:model-value="handleChange"
                        toggleMask
                        fluid
                        class="w-full"
                      />
                      <ErrorMessage class="p-error" name="password_confirmation" />
                    </Field>
                  </div>
                  <div class="flex items-center justify-between mt-2 mb-4 gap-8 warning-password">
                    ※The password must include lowercase letters, uppercase letters, and numbers.<br />
                    ※Please enter at least 8 characters.
                  </div>
                  <div class="flex items-center justify-between mt-2 gap-8 forgot-password">
                    <Link :href="route('admin.login.index')">Back to Login</Link>
                  </div>
                  <Button label="Reset Password" type="submit" icon="pi pi-sign-in" class="w-full mx-auto mt-5"></Button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </VeeForm>
    </template>
  </GuestLayout>
</template>
<style scoped>
.pi-eye {
  transform: scale(1.6);
  margin-right: 1rem;
}

.pi-eye-slash {
  transform: scale(1.6);
  margin-right: 1rem;
}
.warning-password {
  font-size: 12px;
  color: rgb(153, 153, 153);
}
.logo {
  width: 50% !important;
  margin: auto;
}
.forgot-password {
  font-size: 12px;
  font-weight: 400;
  line-height: 20px;
  color: var(--primary-color);
}
</style>
