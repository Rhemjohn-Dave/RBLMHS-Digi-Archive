<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">My Submissions</h1>
    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>
    <div v-else-if="!list.data?.length" class="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500">No submissions yet.</div>
    <ul v-else class="space-y-3">
      <li v-for="r in list.data" :key="r.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="font-medium text-slate-800">{{ r.title }}</h2>
        <p class="mt-1 text-sm text-slate-600">Authors: {{ r.authors }}</p>
        <span :class="statusClass(r.status)" class="mt-2 inline-block rounded px-2 py-0.5 text-sm font-medium">{{ r.status }}</span>
      </li>
    </ul>
    <div v-if="list.data?.length && list.last_page > 1" class="mt-4 flex justify-center gap-2">
      <button :disabled="list.current_page === 1" @click="go(list.current_page - 1)" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50">Prev</button>
      <span class="py-1 text-sm">Page {{ list.current_page }} of {{ list.last_page }}</span>
      <button :disabled="list.current_page === list.last_page" @click="go(list.current_page + 1)" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50">Next</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

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
