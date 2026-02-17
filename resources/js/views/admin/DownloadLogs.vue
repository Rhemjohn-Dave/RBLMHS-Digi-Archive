<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Download Logs</h1>
    <div class="mb-4 flex flex-wrap gap-2">
      <input v-model="search" type="search" placeholder="Search user or research..." class="rounded-lg border border-slate-300 px-3 py-2 w-56" />
      <select v-model="roleFilter" class="rounded-lg border border-slate-300 px-3 py-2">
        <option value="">All roles</option>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
      </select>
      <input v-model="dateFrom" type="date" class="rounded-lg border border-slate-300 px-3 py-2" />
      <input v-model="dateTo" type="date" class="rounded-lg border border-slate-300 px-3 py-2" />
      <button type="button" @click="fetch(1)" class="rounded-lg bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">Filter</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">User</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Research</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Role</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Downloaded At</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">IP</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr v-for="log in list.data" :key="log.id" class="hover:bg-slate-50">
            <td class="px-4 py-2 text-sm">{{ log.user?.username }} ({{ log.user?.given_name }} {{ log.user?.family_name }})</td>
            <td class="px-4 py-2 text-sm">{{ log.research?.title }}</td>
            <td class="px-4 py-2 text-sm">{{ log.role_at_time }}</td>
            <td class="px-4 py-2 text-sm text-slate-600">{{ formatDate(log.downloaded_at) }}</td>
            <td class="px-4 py-2 text-sm text-slate-500">{{ log.ip_address }}</td>
          </tr>
        </tbody>
      </table>
    </div>
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

const search = ref('');
const roleFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const loading = ref(true);
const list = ref({ data: [], current_page: 1, last_page: 1 });

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleString();
}

function fetch(page = 1) {
  loading.value = true;
  const params = { page, per_page: 15 };
  if (search.value) params.search = search.value;
  if (roleFilter.value) params.role = roleFilter.value;
  if (dateFrom.value) params.date_from = dateFrom.value;
  if (dateTo.value) params.date_to = dateTo.value;
  axios.get('/admin/download-logs', { params }).then(({ data }) => {
    list.value = data;
    loading.value = false;
  }).catch(() => { loading.value = false; });
}

onMounted(() => fetch());
</script>
