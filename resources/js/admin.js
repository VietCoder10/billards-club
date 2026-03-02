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
  return /^[A-Za-z0-9]*$/i.test(value);
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
  let special = /[!#$%&*+-=?@_]/g.test(value);

  return lower && upper && number && special;
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
  return /^0(\d-\d{4}-\d{4})+$/i.test(value.trim()) || /^0(\d{3}-\d{2}-\d{4})+$/i.test(value.trim()) || /^(070|080|090|050)(-\d{4}-\d{4})+$/i.test(value.trim()) || /^0(\d{2}-\d{3}-\d{4})+$/i.test(value.trim()) || /^0(\d{9,10})+$/i.test(value.trim());
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
            options: { darkModeSelector: '.app-dark' }
          },
          locale: {
            startsWith: 'Starts with',
            contains: 'Contains',
            notContains: 'Does not contain',
            endsWith: 'Ends with',
            equals: 'Equals',
            notEquals: 'Not equals',
            noFilter: 'No filter',
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
            matchAll: 'Match all',
            matchAny: 'Match any',
            addRule: 'Add rule',
            removeRule: 'Remove rule',
            accept: 'Yes',
            reject: 'No',
            choose: 'Choose',
            upload: 'Upload',
            cancel: 'Cancel',
            dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            today: 'Today',
            weekHeader: 'Week',
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
            am: 'AM',
            pm: 'PM',
            weak: 'Weak',
            medium: 'Medium',
            strong: 'Strong',
            passwordPrompt: 'Enter a password',
            emptyFilterMessage: 'No results found',
            searchMessage: '{0} results are available',
            selectionMessage: '{0} items selected',
            emptySelectionMessage: 'No selected item',
            emptySearchMessage: 'No results found',
            emptyMessage: 'No available options',
            aria: {
              trueLabel: 'True',
              falseLabel: 'False',
              nullLabel: 'Not selected',
              star: '1 star',
              stars: '{star} stars',
              selectAll: 'Select all items',
              unselectAll: 'Unselect all items',
              close: 'Close',
              previous: 'Previous',
              next: 'Next',
              navigation: 'Navigation',
              scrollTop: 'Scroll top',
              moveTop: 'Move top',
              moveUp: 'Move up',
              moveDown: 'Move down',
              moveBottom: 'Move bottom',
              moveToTarget: 'Move to target',
              moveToSource: 'Move to source',
              moveAllToTarget: 'Move all to target',
              moveAllToSource: 'Move all to source',
              pageLabel: '{page}',
              firstPageLabel: 'First page',
              lastPageLabel: 'Last page',
              nextPageLabel: 'Next page',
              prevPageLabel: 'Previous page',
              rowsPerPageLabel: 'Rows per page',
              jumpToPageDropdownLabel: 'Jump to page dropdown',
              jumpToPageInputLabel: 'Jump to page input',
              selectRow: 'Row selected',
              unselectRow: 'Row unselected',
              expandRow: 'Expand row',
              collapseRow: 'Collapse row',
              showFilterMenu: 'Show filter menu',
              hideFilterMenu: 'Hide filter menu',
              filterOperator: 'Filter operator',
              filterConstraint: 'Filter constraint',
              editRow: 'Edit row',
              saveEdit: 'Save edit',
              cancelEdit: 'Cancel edit',
              listView: 'List view',
              gridView: 'Grid view',
              slide: 'Slide',
              slideNumber: 'Slide {slideNumber}',
              zoomImage: 'Zoom image',
              zoomIn: 'Zoom in',
              zoomOut: 'Zoom out',
              rotateRight: 'Rotate right',
              rotateLeft: 'Rotate left'
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

InertiaProgress.init({ color: '#eb142c' });
Inertia.on('start', () => {
  useRequestStore().showLoading();
  NProgress.start();
});
Inertia.on('finish', () => {
  useRequestStore().hideLoading();
  NProgress.done();
});
