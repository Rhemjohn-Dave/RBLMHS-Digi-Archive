<template>
  <div>
    <Breadcrumbs :items="[{ label: 'Admin', to: '/admin' }, { label: 'Download Requests', to: '' }]" class="mb-2" />
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Download Requests</h1>
    <div class="mb-4">
      <select v-model="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2" @change="fetch(1)">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
    <div v-if="loading" class="flex flex-col gap-3 py-8">
      <div v-for="i in 5" :key="i" class="flex gap-4 rounded-lg border border-slate-200 bg-white p-4">
        <div class="h-4 w-24 animate-pulse rounded bg-slate-200" />
        <div class="h-4 flex-1 animate-pulse rounded bg-slate-200" />
        <div class="h-4 w-16 animate-pulse rounded bg-slate-200" />
      </div>
    </div>
    <EmptyState
      v-else-if="!list.data?.length"
      title="No download requests"
      description="Student download requests will appear here."
    />
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow max-h-[70vh] overflow-y-auto">
      <p class="mb-2 text-xs text-slate-500 sm:hidden">Scroll horizontally to see all columns.</p>
      <table class="min-w-[36rem] w-full divide-y divide-slate-200">
        <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">User</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Research</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Status</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Date</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr v-for="p in list.data" :key="p.id" class="transition-colors hover:bg-slate-50/80">
            <td class="px-4 py-3 text-sm">{{ p.user?.username }} ({{ p.user?.given_name }} {{ p.user?.family_name }})</td>
            <td class="px-4 py-3 text-sm">{{ p.research?.title }}</td>
            <td class="px-4 py-3">
              <span :class="p.status === 'approved' ? 'bg-green-100 text-green-800' : p.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'" class="rounded px-2 py-0.5 text-xs font-medium">{{ p.status }}</span>
            </td>
            <td class="px-4 py-3 text-sm text-slate-500">{{ formatDate(p.created_at) }}</td>
            <td class="px-4 py-3">
              <template v-if="p.status === 'pending'">
                <button type="button" @click="approve(p.id)" class="rounded p-1.5 text-green-600 hover:bg-green-50 transition-colors" title="Approve" aria-label="Approve request">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </button>
                <button type="button" @click="reject(p.id)" class="rounded p-1.5 text-red-600 hover:bg-red-50 transition-colors" title="Reject" aria-label="Reject request">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </template>
            </td>
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
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToast } from '../../composables/useToast';
import Breadcrumbs from '../../components/Breadcrumbs.vue';
import EmptyState from '../../components/EmptyState.vue';
import Pagination from '../../components/Pagination.vue';

const toast = useToast();
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
  axios.post(`/admin/download-permissions/${id}/approve`).then(() => {
    toast.success('Download permission approved.');
    fetch(list.value.current_page);
  }).catch(() => toast.error('Failed to approve request.'));
}

function reject(id) {
  axios.post(`/admin/download-permissions/${id}/reject`).then(() => {
    toast.success('Download permission rejected.');
    fetch(list.value.current_page);
  }).catch(() => toast.error('Failed to reject request.'));
}

onMounted(() => fetch());
watch(statusFilter, () => fetch(1));
</script>
