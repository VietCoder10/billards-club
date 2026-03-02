<script setup>
import logo from '@/assets/imgs/logo.svg';
import GuestLayout from '@/Layouts/Admin/GuestLayout.vue';

import { useForm, Link } from '@inertiajs/inertia-vue3';
import { ref, reactive, onMounted, watch } from 'vue';
import $ from 'jquery';
import { Form as VeeForm, Field, ErrorMessage, defineRule, configure } from 'vee-validate';
import { localize } from '@vee-validate/i18n';
const state = reactive({
  model: {
    email: ''
  }
});
const props = defineProps(['data']);
const onSubmit = () => {
  useForm(state.model).post(route('admin.forgot-password.store'));
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
onMounted(() => {
  state.model.url_redirect = props.data.request.url_redirect;
});
watch(
  () => props.data,
  () => {
    state.message = props.data.message;
  }
);
let messError = {
  en: {
    fields: {
      email: {
        required: 'Please enter a value.',
        email: 'Please enter a valid email address (xxx@yyyy.zzz).',
        max: 'Please enter 255 characters or fewer.'
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
                    <div class="text-2xl font-bold text-red-400 drop-shadow-md tracking-wide uppercase select-none">Việt Vũ Billards</div>
                  </div>

                  <div class="mb-4">
                    <div>
                      <label for="email" class="block text-surface-900 dark:text-surface-0 mb-1">Email</label>
                      <Field name="email" rules="required|max:255|email" v-model="state.model.email" placeholder="Email" v-slot="{ field, meta: metaField, handleChange }">
                        <InputText
                          v-model="state.model.email"
                          v-bind="field"
                          :class="{
                            'p-invalid': !metaField.valid && metaField.touched
                          }"
                          @update:model-value="handleChange"
                          class="w-full md:w-[30rem]"
                          placeholder="Email"
                        />
                      </Field>
                    </div>
                    <ErrorMessage class="p-error" name="email" />
                  </div>
                  <div class="flex items-center justify-between mt-2 gap-8 forgot-password">
                    <Link :href="route('admin.login.index')">Back to Login</Link>
                  </div>
                  <Button label="Forgot Password" type="submit" icon="pi pi-sign-in" class="w-full mx-auto mt-5"></Button>
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
