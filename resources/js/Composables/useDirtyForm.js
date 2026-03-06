import { onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';

export function useDirtyForm(hasUnsavedChanges, isSubmitting) {
  const handleBeforeUnload = (event) => {
    if (hasUnsavedChanges.value && !isSubmitting.value) {
      event.preventDefault();
      event.returnValue = '';
    }
  };

  onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
  });

  const unsubscribe = router.on('before', (event) => {
    if (hasUnsavedChanges.value && !isSubmitting.value) {
      event.preventDefault();
      window.location.href = event.detail.visit.url;
    }
  });

  onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
    unsubscribe();
  });
}
