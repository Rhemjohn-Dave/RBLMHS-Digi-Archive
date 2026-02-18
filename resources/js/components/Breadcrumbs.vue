<template>
  <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm text-slate-600">
    <template v-for="(item, i) in items" :key="i">
      <router-link
        v-if="i < items.length - 1"
        :to="item.to"
        class="transition-colors hover:text-slate-900"
      >
        {{ item.label }}
      </router-link>
      <span
        v-else
        class="font-medium text-slate-800"
        aria-current="page"
      >
        {{ item.label }}
      </span>
      <span v-if="i < items.length - 1" class="text-slate-400" aria-hidden="true">/</span>
    </template>
  </nav>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
    /** [{ label: string, to: string }] */
  },
});
</script>
