<template>
  <div
    v-if="lastPage > 1"
    class="flex flex-wrap items-center justify-center gap-2 text-sm text-slate-600"
    role="navigation"
    aria-label="Pagination"
  >
    <button
      type="button"
      :disabled="currentPage <= 1"
      :aria-disabled="currentPage <= 1"
      @click="go(1)"
      class="rounded-full border border-slate-300 px-3 py-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
      aria-label="First page"
    >
      First
    </button>
    <button
      type="button"
      :disabled="currentPage <= 1"
      :aria-disabled="currentPage <= 1"
      @click="go(currentPage - 1)"
      class="rounded-full border border-slate-300 px-3 py-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
      aria-label="Previous page"
    >
      Prev
    </button>
    <span class="px-2" aria-live="polite">
      Page
      <span class="font-semibold text-slate-900">{{ currentPage }}</span>
      of
      <span class="font-semibold text-slate-900">{{ lastPage }}</span>
    </span>
    <button
      type="button"
      :disabled="currentPage >= lastPage"
      :aria-disabled="currentPage >= lastPage"
      @click="go(currentPage + 1)"
      class="rounded-full border border-slate-300 px-3 py-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
      aria-label="Next page"
    >
      Next
    </button>
    <button
      type="button"
      :disabled="currentPage >= lastPage"
      :aria-disabled="currentPage >= lastPage"
      @click="go(lastPage)"
      class="rounded-full border border-slate-300 px-3 py-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
      aria-label="Last page"
    >
      Last
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
});

const emit = defineEmits(['page']);

function go(page) {
  if (page < 1 || page > props.lastPage) return;
  emit('page', page);
}
</script>
