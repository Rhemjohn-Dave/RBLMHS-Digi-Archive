import { ref, readonly } from 'vue';

const toasts = ref([]);
let id = 0;
let hideTimer = null;

export function useToast() {
  function add(options) {
    const toastId = ++id;
    const entry = {
      id: toastId,
      type: options.type ?? 'info',
      message: options.message ?? '',
      duration: options.duration ?? 4000,
      ...options,
    };
    toasts.value = [...toasts.value, entry];

    if (entry.duration > 0) {
      const t = setTimeout(() => {
        remove(toastId);
      }, entry.duration);
      entry._timer = t;
    }
    return toastId;
  }

  function remove(toastId) {
    const entry = toasts.value.find((t) => t.id === toastId);
    if (entry?._timer) clearTimeout(entry._timer);
    toasts.value = toasts.value.filter((t) => t.id !== toastId);
  }

  function success(message, duration) {
    return add({ type: 'success', message, duration });
  }
  function error(message, duration) {
    return add({ type: 'error', message, duration: duration ?? 6000 });
  }
  function info(message, duration) {
    return add({ type: 'info', message, duration });
  }

  return {
    toasts: readonly(toasts),
    add,
    remove,
    success,
    error,
    info,
  };
}
