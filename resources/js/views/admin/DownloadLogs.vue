<template>
  <div>
    <Breadcrumbs :items="[{ label: 'Admin', to: '/admin' }, { label: 'Download Logs', to: '' }]" class="mb-2" />
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
    <div v-if="loading" class="flex flex-col gap-3 py-8">
      <div v-for="i in 5" :key="i" class="flex gap-4 rounded-lg border border-slate-200 bg-white p-4">
        <div class="h-4 w-24 animate-pulse rounded bg-slate-200" />
        <div class="h-4 flex-1 animate-pulse rounded bg-slate-200" />
        <div class="h-4 w-20 animate-pulse rounded bg-slate-200" />
      </div>
    </div>
    <EmptyState
      v-else-if="!list.data?.length"
      title="No download logs"
      description="Download activity will appear here."
    />
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow max-h-[70vh] overflow-y-auto">
      <p class="mb-2 text-xs text-slate-500 sm:hidden">Scroll horizontally to see all columns.</p>
      <table class="min-w-[36rem] w-full divide-y divide-slate-200">
        <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">User</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Research</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Role</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Downloaded At</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">IP</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr v-for="log in list.data" :key="log.id" class="transition-colors hover:bg-slate-50/80">
            <td class="px-4 py-3 text-sm">{{ log.user?.username }} ({{ log.user?.given_name }} {{ log.user?.family_name }})</td>
            <td class="px-4 py-2 text-sm">{{ log.research?.title }}</td>
            <td class="px-4 py-3 text-sm">{{ log.role_at_time }}</td>
            <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(log.downloaded_at) }}</td>
            <td class="px-4 py-3 text-sm text-slate-500">{{ log.ip_address }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination
      v-if="list.data?.length"
      :current-page="list.current_page"
      :last-page="list.last_page"
      @page="(p) => fetch(p)"
      class="mt-4"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Breadcrumbs from '../../components/Breadcrumbs.vue';
import EmptyState from '../../components/EmptyState.vue';
import Pagination from '../../components/Pagination.vue';

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
