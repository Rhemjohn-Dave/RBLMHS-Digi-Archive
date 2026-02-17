<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Download Requests</h1>
    <div class="mb-4">
      <select v-model="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2" @change="fetch(1)">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">User</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Research</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Status</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Date</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr v-for="p in list.data" :key="p.id" class="hover:bg-slate-50">
            <td class="px-4 py-2 text-sm">{{ p.user?.username }} ({{ p.user?.given_name }} {{ p.user?.family_name }})</td>
            <td class="px-4 py-2 text-sm">{{ p.research?.title }}</td>
            <td class="px-4 py-2">
              <span :class="p.status === 'approved' ? 'bg-green-100 text-green-800' : p.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'" class="rounded px-2 py-0.5 text-xs font-medium">{{ p.status }}</span>
            </td>
            <td class="px-4 py-2 text-sm text-slate-500">{{ formatDate(p.created_at) }}</td>
            <td class="px-4 py-2">
              <template v-if="p.status === 'pending'">
                <button type="button" @click="approve(p.id)" class="mr-2 text-sm text-green-600 hover:underline">Approve</button>
                <button type="button" @click="reject(p.id)" class="text-sm text-red-600 hover:underline">Reject</button>
              </template>
            </td>
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
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const statusFilter = ref('pending');
const loading = ref(true);
const list = ref({ data: [], current_page: 1, last_page: 1 });

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleString();
}

function fetch(page = 1) {
  loading.value = true;
  const params = { page, per_page: 15 };
  if (statusFilter.value) params.status = statusFilter.value;
  axios.get('/admin/download-requests', { params }).then(({ data }) => {
    list.value = data;
    loading.value = false;
  }).catch(() => { loading.value = false; });
}

function approve(id) {
  axios.post(`/admin/download-permissions/${id}/approve`).then(() => fetch(list.value.current_page));
}

function reject(id) {
  axios.post(`/admin/download-permissions/${id}/reject`).then(() => fetch(list.value.current_page));
}

onMounted(() => fetch());
watch(statusFilter, () => fetch(1));
</script>
