import '@/assets/styles.scss';
import Lara from '@primeuix/themes/lara';
import moment from 'moment';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';

import * as rules from '@vee-validate/rules';
import { configure, defineRule } from 'vee-validate';
configure({
  validateOnBlur: false,
  validateOnChange: false,
  validateOnInput: true,
  validateOnModelUpdate: false
});
const app = createApp({});

Object.keys(rules).forEach((rule) => {
  if (rule != 'default' && rule != 'all') {
    defineRule(rule, rules[rule]);
  }
});
defineRule('custom_confirm', (value, arg) => {
  return /^[A-Za-z0-9]*$/i.test(value);
});
defineRule('code_rule', (value) => {
  if (!value) {
    return true;
  }
  return /^[A-Za-z\-0-9]*$/i.test(value);
});
defineRule('password_rule_admin', (value) => {
  return /^[A-Za-z0-9]*$/i.test(value);
});
defineRule('date_format', (value) => {
  if (!value) {
    return true;
  }
  return moment(value, 'YYYY/MM/DD', true).isValid();
});

defineRule('password_rule', (value) => {
  if (!value) {
    return true;
  }
  let lower = /[a-z]/g.test(value);
  let upper = /[A-Z]/g.test(value);
  let number = /[0-9]/g.test(value);

  return lower && upper && number;
});
defineRule('password_str', (value) => {
  if (!value) {
    return true;
  }
  let lower = /[a-z]/g.test(value);
  let upper = /[A-Z]/g.test(value);

  return lower && upper;
});
defineRule('password_number', (value) => {
  if (!value) {
    return true;
  }
  let number = /[0-9]/g.test(value);

  return number;
});
defineRule('telephone', (value) => {
  if (!value) {
    return true;
  }
  return (
    /^0(\d-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{3}-\d{2}-\d{4})+$/i.test(value.trim()) ||
    /^(070|080|090|050)(-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{2}-\d{3}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{9,10})+$/i.test(value.trim()) ||
    /^\+\d{2}\d{9,10}$/i.test(value.trim()) ||
    /^\(\+\d{2}\)\d{9,10}$/i.test(value.trim())
  );
});
defineRule('tel', (value) => {
  if (!value) {
    return true;
  }
  return (
    /^0(\d-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{3}-\d{2}-\d{4})+$/i.test(value.trim()) ||
    /^(070|080|090|050)(-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{2}-\d{3}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{9,10})+$/i.test(value.trim()) ||
    /^\+\d{2}\d{9,10}$/i.test(value.trim()) ||
    /^\(\+\d{2}\)\d{9,10}$/i.test(value.trim())
  );
});
defineRule('mobile', (value) => {
  if (!value) {
    return true;
  }
  return (
    /^0(\d-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{3}-\d{2}-\d{4})+$/i.test(value.trim()) ||
    /^(070|080|090|050)(-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{2}-\d{3}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{9,10})+$/i.test(value.trim()) ||
    /^\+\d{2}\d{9,10}$/i.test(value.trim()) ||
    /^\(\+\d{2}\)\d{9,10}$/i.test(value.trim())
  );
});
defineRule('fax', (value) => {
  if (!value) {
    return true;
  }
  return (
    /^0(\d-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{3}-\d{2}-\d{4})+$/i.test(value.trim()) ||
    /^(070|080|090|050)(-\d{4}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{2}-\d{3}-\d{4})+$/i.test(value.trim()) ||
    /^0(\d{9,10})+$/i.test(value.trim()) ||
    /^\+\d{2}\d{9,10}$/i.test(value.trim()) ||
    /^\(\+\d{2}\)\d{9,10}$/i.test(value.trim())
  );
});
defineRule('kata', (value) => {
  if (!value) {
    return true;
  }
  return /^([ァ-ンｧ-ﾝﾞﾟ]|ー|　| |（|）|\(|\))*$/i.test(value);
});
defineRule('hiragana', (value) => {
  if (!value) {
    return true;
  }
  return /^([ぁ-ん]|ー|　| |（|）|\(|\))*$/i.test(value);
});

import BtnAction from '@/Components/Common/BtnAction.vue';
import DataEmpty from '@/Components/Common/DataEmpty.vue';
import FormSearch from '@/Components/Common/FormSearch.vue';
import GenerateSort from '@/Components/Common/GenerateSort.vue';
import LimitPageOption from '@/Components/Common/LimitPageOption.vue';
import Notyf from '@/Components/Common/Notyf.vue';
import Paginator from '@/Components/Common/Paginator.vue';
import { useRequestStore } from '@/store/request';
import { Inertia } from '@inertiajs/inertia';
import { createInertiaApp, Link } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import NProgress from 'nprogress';
import ConfirmationService from 'primevue/confirmationservice';
import ProgressSpinner from 'primevue/progressspinner';
import StyleClass from 'primevue/styleclass';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.js';

app.component('notyf', Notyf);
const appName = 'Billards';

createInertiaApp({
  title: (title) => `${title} | ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'), import.meta.glob('./Pages/**/**/*.vue')),
  setup({ el, app, props, plugin }) {
    return (
      createApp({ render: () => h(app, props) })
        .use(plugin)
        .use(ConfirmationService)
        .use(PrimeVue, {
          theme: {
            preset: Lara,
            options: {
              darkModeSelector: '.app-dark'
            }
          },
          locale: {
            startsWith: 'Starts with',
            contains: 'Contains',
            notContains: 'Not contains',
            endsWith: 'Ends with',
            equals: 'Equals',
            notEquals: 'Not equals',
            noFilter: 'No Filter',
            lt: 'Less than',
            lte: 'Less than or equal to',
            gt: 'Greater than',
            gte: 'Greater than or equal to',
            dateIs: 'Date is',
            dateIsNot: 'Date is not',
            dateBefore: 'Date is before',
            dateAfter: 'Date is after',
            clear: 'Clear',
            apply: 'Apply',
            matchAll: 'Match All',
            matchAny: 'Match Any',
            addRule: 'Add Rule',
            removeRule: 'Remove Rule',
            accept: 'Yes',
            reject: 'No',
            choose: 'Choose',
            upload: 'Upload',
            cancel: 'Cancel',
            dayNames: ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'],
            dayNamesShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            dayNamesMin: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            monthNamesShort: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            today: 'Hôm nay',
            weekHeader: 'Tuần',
            firstDayOfWeek: 0,
            dateFormat: 'yy-mm-dd',
            chooseYear: 'Choose Year',
            chooseMonth: 'Choose Month',
            chooseDate: 'Choose Date',
            prevDecade: 'Previous Decade',
            nextDecade: 'Next Decade',
            prevYear: 'Previous Year',
            nextYear: 'Next Year',
            prevMonth: 'Previous Month',
            nextMonth: 'Next Month',
            prevHour: 'Previous Hour',
            nextHour: 'Next Hour',
            prevMinute: 'Previous Minute',
            nextMinute: 'Next Minute',
            prevSecond: 'Previous Second',
            nextSecond: 'Next Second',
            am: 'am',
            pm: 'pm',
            weak: 'Weak',
            medium: 'Medium',
            strong: 'Strong',
            passwordPrompt: 'Enter a password',
            emptyFilterMessage: 'No results found', // @deprecated Use 'emptySearchMessage' option instead.
            searchMessage: '{0} results are available',
            selectionMessage: '{0} items selected',
            emptySelectionMessage: 'No selected item',
            emptySearchMessage: 'No results found',
            emptyMessage: 'No available options',
            aria: {
              trueLabel: 'True',
              falseLabel: 'False',
              nullLabel: 'Not Selected',
              star: '1 star',
              stars: '{star} stars',
              selectAll: 'All items selected',
              unselectAll: 'All items unselected',
              close: 'Close',
              previous: 'Previous',
              next: 'Next',
              navigation: 'Navigation',
              scrollTop: 'Scroll Top',
              moveTop: 'Move Top',
              moveUp: 'Move Up',
              moveDown: 'Move Down',
              moveBottom: 'Move Bottom',
              moveToTarget: 'Move to Target',
              moveToSource: 'Move to Source',
              moveAllToTarget: 'Move All to Target',
              moveAllToSource: 'Move All to Source',
              pageLabel: '{page}',
              firstPageLabel: 'First Page',
              lastPageLabel: 'Last Page',
              nextPageLabel: 'Next Page',
              prevPageLabel: 'Previous Page',
              rowsPerPageLabel: 'Rows per page',
              jumpToPageDropdownLabel: 'Jump to Page Dropdown',
              jumpToPageInputLabel: 'Jump to Page Input',
              selectRow: 'Row Selected',
              unselectRow: 'Row Unselected',
              expandRow: 'Row Expanded',
              collapseRow: 'Row Collapsed',
              showFilterMenu: 'Show Filter Menu',
              hideFilterMenu: 'Hide Filter Menu',
              filterOperator: 'Filter Operator',
              filterConstraint: 'Filter Constraint',
              editRow: 'Row Edit',
              saveEdit: 'Save Edit',
              cancelEdit: 'Cancel Edit',
              listView: 'List View',
              gridView: 'Grid View',
              slide: 'Slide',
              slideNumber: '{slideNumber}',
              zoomImage: 'Zoom Image',
              zoomIn: 'Zoom In',
              zoomOut: 'Zoom Out',
              rotateRight: 'Rotate Right',
              rotateLeft: 'Rotate Left'
            }
          }
        })
        .use(ToastService)
        .use(createPinia())
        // .component('InputText', InputText)
        .component('GenerateSort', GenerateSort)
        .component('BtnAction', BtnAction)
        .component('DataEmpty', DataEmpty)
        .component('Link', Link)
        .component('ProgressSpinner', ProgressSpinner)
        .component('PaginatorCustom', Paginator)
        .component('LimitPageOption', LimitPageOption)
        .component('Toast', Toast)
        .component('FormSearch', FormSearch)
        .component('useRequestStore', useRequestStore)
        .directive('styleclass', StyleClass)

        .use(ZiggyVue, Ziggy, Notyf)
        .mount(el)
    );
  }
});

InertiaProgress.init({ color: '#163374' });
Inertia.on('start', () => {
  useRequestStore().showLoading();
  NProgress.start();
});
Inertia.on('finish', () => {
  useRequestStore().hideLoading();
  NProgress.done();
});

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/serviceworker.js');
}
