<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Pending Research</h1>
    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>
    <div v-else-if="!list.data?.length" class="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500">No pending research.</div>
    <ul v-else class="space-y-4">
      <li v-for="r in list.data" :key="r.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="font-medium text-slate-800">{{ r.title }}</h2>
        <p class="mt-1 text-sm text-slate-600">Authors: {{ r.authors }}</p>
        <p class="text-sm text-slate-500">Submitted by: {{ r.faculty?.given_name }} {{ r.faculty?.family_name }}</p>
        <div class="mt-3 flex gap-2">
          <button type="button" @click="approve(r.id)" class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-500">Approve</button>
          <button type="button" @click="reject(r.id)" class="rounded bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-500">Reject</button>
        </div>
      </li>
    </ul>
    <div v-if="list.data?.length && list.last_page > 1" class="mt-4 flex justify-center gap-2">
      <button :disabled="list.current_page === 1" @click="fetch(list.current_page - 1)" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50">Prev</button>
      <span class="py-1 text-sm">Page {{ list.current_page }} of {{ list.last_page }}</span>
      <button :disabled="list.current_page === list.last_page" @click="fetch(list.current_page + 1)" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50">Next</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const list = ref({ data: [], current_page: 1, last_page: 1 });

function fetch(page = 1) {
  loading.value = true;
  axios.get('/admin/research-pending', { params: { page, per_page: 15 } }).then(({ data }) => {
    list.value = data;
    loading.value = false;
  }).catch(() => { loading.value = false; });
}

function approve(id) {
  axios.post(`/admin/research/${id}/approve`).then(() => fetch(list.value.current_page));
}

function reject(id) {
  axios.post(`/admin/research/${id}/reject`).then(() => fetch(list.value.current_page));
}

onMounted(() => fetch());
</script>
