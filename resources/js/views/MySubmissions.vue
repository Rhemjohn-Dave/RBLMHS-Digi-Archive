<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">My Submissions</h1>
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <Skeleton variant="title" class="mb-2" />
        <Skeleton variant="text" class="h-4 w-3/4" />
        <Skeleton class="mt-2 h-6 w-20 rounded-full" />
      </div>
    </div>
    <EmptyState
      v-else-if="!list.data?.length"
      title="No submissions yet"
      description="Upload your first research to submit for approval."
    >
      <template #action>
        <router-link
          to="/upload"
          class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
          Upload Research
        </router-link>
      </template>
    </EmptyState>
    <ul v-else class="space-y-3">
      <li v-for="r in list.data" :key="r.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="font-medium text-slate-800">{{ r.title }}</h2>
        <p class="mt-1 text-sm text-slate-600">Authors: {{ r.authors }}</p>
        <span :class="statusClass(r.status)" class="mt-2 inline-block rounded px-2 py-0.5 text-sm font-medium">{{ r.status }}</span>
      </li>
    </ul>
    <Pagination
      v-if="list.data?.length"
      :current-page="list.current_page"
      :last-page="list.last_page"
      @page="go"
      class="mt-4"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Skeleton from '../components/Skeleton.vue';
import EmptyState from '../components/EmptyState.vue';
import Pagination from '../components/Pagination.vue';

const loading = ref(true);
const list = ref({ data: [], current_page: 1, last_page: 1 });

function statusClass(s) {
  if (s === 'approved') return 'bg-green-100 text-green-800';
  if (s === 'rejected') return 'bg-red-100 text-red-800';
  return 'bg-amber-100 text-amber-800';
}

function fetch(page = 1) {
  loading.value = true;
  axios.get('/my-submissions', { params: { page, per_page: 15 } }).then(({ data }) => {
    list.value = data;
    loading.value = false;
  }).catch(() => { loading.value = false; });
}

function go(p) {
  if (p < 1 || p > list.value.last_page) return;
  fetch(p);
}

onMounted(() => fetch());
</script>
