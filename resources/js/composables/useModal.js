import { ref, computed } from 'vue';

export function useModal(initialState = false) {
  const isOpen = ref(initialState);
  const data = ref(null);

  const open = (modalData = null) => {
    isOpen.value = true;
    data.value = modalData;
    document.body.style.overflow = 'hidden';
  };

  const close = () => {
    isOpen.value = false;
    data.value = null;
    document.body.style.overflow = '';
  };

  const toggle = () => {
    if (isOpen.value) {
      close();
    } else {
      open();
    }
  };

  return {
    isOpen: computed(() => isOpen.value),
    data: computed(() => data.value),
    open,
    close,
    toggle
  };
}

export function useConfirmModal() {
  const { isOpen, data, open, close } = useModal();
  const resolve = ref(null);
  const reject = ref(null);

  const confirm = (message, title = 'Confirm Action') => {
    return new Promise((res, rej) => {
      resolve.value = res;
      reject.value = rej;
      open({ message, title });
    });
  };

  const onConfirm = () => {
    if (resolve.value) {
      resolve.value(true);
    }
    close();
  };

  const onCancel = () => {
    if (reject.value) {
      reject.value(false);
    }
    close();
  };

  return {
    isOpen,
    data,
    confirm,
    onConfirm,
    onCancel,
    close
  };
}
