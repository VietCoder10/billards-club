<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { Field, ErrorMessage, Form as VeeForm, configure } from 'vee-validate';
import { localize, setLocale } from '@vee-validate/i18n';
import { useToastStore } from '@/store/toast';
import { useRequestStore } from '@/store/request';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import FloatLabel from 'primevue/floatlabel';

const toastStore = useToastStore();
const requestStore = useRequestStore();

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible', 'saved']);

const localVisible = ref(props.visible);

watch(
    () => props.visible,
    (v) => {
        state.model = { ...initialState };
        localVisible.value = v;
    }
);

watch(localVisible, (v) => emit('update:visible', v));

const initialState = {
    name: '',
    email: '',
    tel: '',
    password: ''
};

const state = reactive({
    model: { ...initialState }
});

const handleCancel = () => {
    localVisible.value = false;
};

onMounted(() => {
    setMessageError();
});

const setMessageError = () => {
    let messError = {
        ja: {
            fields: {
                name: {
                    required: '名前を入力してください。',
                    max: '名前は255文字を超えてはなりません。'
                },
                email: {
                    required: 'メールアドレスを入力してください。',
                    email: 'メールアドレスの形式で入力してください。',
                    max: 'メールアドレスは255文字を超えてはなりません。'
                },
                tel: {
                    required: '電話番号を入力してください。',
                    max: '電話番号は255文字を超えてはなりません。'
                },
                password: {
                    required: 'パスワードを入力してください。',
                    min: 'パスワードは8文字以上で入力してください。'
                }
            }
        }
    };

    configure({
        generateMessage: localize(messError)
    });
};
setLocale('ja');

const onSubmit = async () => {
    requestStore.showLoading();
    try {
        const response = await axios.post(
            route('admin.customer.storeModel'),
            { ...state.model },
            {
                headers: {
                    Accept: 'application/json'
                }
            }
        );
        if (response?.data?.message) {
            toastStore.setToast(response.data.message, 'success');
        }
        localVisible.value = false;
        emit('saved');
    } catch (error) {
        toastStore.setToast('エラーが発生しました。', 'error');
    } finally {
        requestStore.hideLoading();
    }
};

</script>

<template>
    <Dialog v-model:visible="localVisible" modal header="新規顧客登録" :dismissableMask="true" :style="{ width: '40vw' }"
        :breakpoints="{ '960px': '60vw', '640px': '90vw' }">
        <VeeForm as="div" v-slot="{ handleSubmit }">
            <form @submit="handleSubmit($event, onSubmit)" class="space-y-6 py-4">
                <div class="field">
                    <Field name="name" rules="required|max:255" v-model="state.model.name"
                        v-slot="{ field, meta, handleChange }">
                        <FloatLabel variant="on">
                            <InputText id="add_name" class="w-full" :modelValue="field.value"
                                @update:modelValue="handleChange"
                                :class="{ 'p-invalid': !meta.valid && meta.touched }" />
                            <label for="add_name">名前<span class="text-red-500">(必須)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error text-xs" name="name" />
                    </Field>
                </div>

                <div class="field">
                    <Field name="email" rules="required|email|max:255" v-model="state.model.email"
                        v-slot="{ field, meta, handleChange }">
                        <FloatLabel variant="on">
                            <InputText id="add_email" class="w-full" :modelValue="field.value"
                                @update:modelValue="handleChange"
                                :class="{ 'p-invalid': !meta.valid && meta.touched }" />
                            <label for="add_email">メールアドレス<span class="text-red-500">(必須)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error text-xs" name="email" />
                    </Field>
                </div>

                <div class="field">
                    <Field name="tel" rules="required|max:255" v-model="state.model.tel"
                        v-slot="{ field, meta, handleChange }">
                        <FloatLabel variant="on">
                            <InputText id="add_tel" class="w-full" :modelValue="field.value"
                                @update:modelValue="handleChange"
                                :class="{ 'p-invalid': !meta.valid && meta.touched }" />
                            <label for="add_tel">電話番号<span class="text-red-500">(必須)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error text-xs" name="tel" />
                    </Field>
                </div>

                <div class="field">
                    <Field name="password" rules="required|min:8" v-model="state.model.password"
                        v-slot="{ field, meta, handleChange }">
                        <FloatLabel variant="on">
                            <InputText id="add_password" type="password" class="w-full" :modelValue="field.value"
                                @update:modelValue="handleChange"
                                :class="{ 'p-invalid': !meta.valid && meta.touched }" />
                            <label for="add_password">パスワード<span class="text-red-500">(必須)</span></label>
                        </FloatLabel>
                        <ErrorMessage class="p-error text-xs" name="password" />
                    </Field>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button label="キャンセル" icon="pi pi-times" severity="secondary" @click="handleCancel" />
                    <Button label="保存" icon="pi pi-save" type="submit" />
                </div>
            </form>
        </VeeForm>
    </Dialog>
</template>
