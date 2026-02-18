<template>
  <div
    class="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-12 text-center"
    :class="$attrs.class"
  >
    <span
      class="mb-4 flex h-14 w-14 shrink-0 items-center justify-center rounded-full text-slate-300"
      :class="iconBgClass"
      aria-hidden="true"
    >
      <slot name="icon">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
      </slot>
    </span>
    <h3 v-if="title" class="text-base font-semibold text-slate-800">{{ title }}</h3>
    <p v-if="description" class="mt-1 max-w-sm text-sm text-slate-500">{{ description }}</p>
    <div v-if="$slots.action" class="mt-5">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  variant: { type: String, default: 'default' },
});

const iconBgClass = computed(() =>
  props.variant === 'default' ? 'bg-slate-100' : 'bg-slate-50'
);
</script>
