<template>
  <div
    class="pointer-events-none fixed bottom-0 left-0 right-0 z-[100] flex flex-col items-center gap-2 pb-6 px-4 sm:pb-8 sm:px-6"
    role="status"
    aria-live="polite"
    aria-atomic="false"
  >
    <TransitionGroup name="toast" tag="div" class="flex flex-col items-center gap-2 w-full max-w-md">
      <div
        v-for="t in toasts"
        :key="t.id"
        class="pointer-events-auto w-full rounded-lg border shadow-lg flex items-start gap-3 p-3 transition-all duration-200"
        :class="toastClass(t.type)"
      >
        <span class="shrink-0 mt-0.5" aria-hidden="true">
          <svg v-if="t.type === 'success'" class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else-if="t.type === 'error'" class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </span>
        <p class="flex-1 text-sm font-medium text-slate-800">{{ t.message }}</p>
        <button
          type="button"
          @click="remove(t.id)"
          class="shrink-0 rounded p-1 text-slate-400 hover:bg-slate-200/80 hover:text-slate-600 transition-colors"
          aria-label="Dismiss"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '../composables/useToast';

const { toasts, remove } = useToast();

function toastClass(type) {
  if (type === 'success') return 'bg-white border-emerald-200 bg-emerald-50/90';
  if (type === 'error') return 'bg-white border-red-200 bg-red-50/90';
  return 'bg-white border-slate-200';
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(0.5rem);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-0.25rem);
}
</style>
