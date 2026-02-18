<template>
  <div
    class="animate-pulse rounded-md bg-slate-200"
    :class="[heightClass, widthClass, roundedClass, $attrs.class]"
    :style="$attrs.style"
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** Preset: 'text' | 'title' | 'avatar' | 'card' | 'line' | 'custom' */
  variant: { type: String, default: 'line' },
  /** Tailwind height class, e.g. 'h-4' */
  height: { type: String, default: '' },
  /** Tailwind width class, e.g. 'w-32' */
  width: { type: String, default: '' },
});

const heightClass = computed(() => {
  if (props.height) return props.height;
  const map = { text: 'h-4', title: 'h-6', avatar: 'h-10', card: 'h-24', line: 'h-4' };
  return map[props.variant] || 'h-4';
});

const widthClass = computed(() => {
  if (props.width) return props.width;
  const map = { text: 'w-full', title: 'w-3/4', avatar: 'w-10', card: 'w-full', line: 'w-full' };
  return map[props.variant] || 'w-full';
});

const roundedClass = computed(() => (props.variant === 'avatar' ? 'rounded-full' : ''));
</script>
