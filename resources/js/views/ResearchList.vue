<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Research Repository</h1>
    <div class="mb-4 flex flex-wrap gap-2">
      <input v-model="search" type="search" placeholder="Search title, authors..." class="rounded-lg border border-slate-300 px-3 py-2 w-64" />
      <input v-model="year" type="number" placeholder="Year" class="rounded-lg border border-slate-300 px-3 py-2 w-24" min="1900" :max="new Date().getFullYear()" />
      <button type="button" @click="fetch" class="rounded-lg bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">Search</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>
    <div v-else-if="!list.data?.length" class="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500">No research found.</div>
    <ul v-else class="space-y-3">
      <li v-for="r in list.data" :key="r.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <router-link :to="'/research/' + r.id" class="block">
          <h2 class="font-medium text-slate-800 hover:underline">{{ r.title }}</h2>
          <p class="mt-1 text-sm text-slate-600">Authors: {{ r.authors }}</p>
          <p v-if="r.published_date" class="text-sm text-slate-500">{{ formatDate(r.published_date) }}</p>
        </router-link>
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

const search = ref('');
const year = ref('');
const loading = ref(true);
const list = ref({ data: [], current_page: 1, last_page: 1 });

function formatDate(d) {
  if (!d) return '';
  const x = new Date(d);
  return x.toLocaleDateString();
}

function fetch(page = 1) {
  loading.value = true;
  const params = { page, per_page: 15 };
  if (search.value) params.search = search.value;
  if (year.value) params.year = year.value;
  axios.get('/research', { params }).then(({ data }) => {
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
