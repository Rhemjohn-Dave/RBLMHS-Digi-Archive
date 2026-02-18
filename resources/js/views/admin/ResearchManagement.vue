<template>
  <div class="space-y-4">
    <Breadcrumbs :items="[{ label: 'Admin', to: '/admin' }, { label: 'Research', to: '' }]" class="mb-2" />
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">
          Pending Research
        </h1>
        <p class="mt-1 text-sm text-slate-600">
          Review and approve research submissions from faculty members.
        </p>
      </div>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <Skeleton variant="title" class="mb-2" />
        <Skeleton variant="text" class="!w-3/4" />
      </div>
    </div>
    <EmptyState
      v-else-if="!list.data?.length"
      title="No pending research"
      description="Research submissions from faculty will appear here for approval."
    />

    <!-- List -->
    <ul v-else class="space-y-4">
      <li
        v-for="r in list.data"
        :key="r.id"
        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md"
      >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h2 class="text-base font-semibold text-slate-900">
              {{ r.title }}
            </h2>
            <p class="mt-1 text-sm text-slate-600">
              <span class="font-medium text-slate-700">Authors:</span>
              {{ r.authors || 'N/A' }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
              Submitted by:
              <span class="font-medium text-slate-700">
                {{ r.faculty?.given_name }} {{ r.faculty?.family_name }}
              </span>
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="approve(r.id)"
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500 transition-colors"
              title="Approve"
              aria-label="Approve research"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              Approve
            </button>
            <button
              type="button"
              @click="reject(r.id)"
              class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-500 transition-colors"
              title="Reject"
              aria-label="Reject research"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              Reject
            </button>
          </div>
        </div>
      </li>
    </ul>

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
import { useToast } from '../../composables/useToast';
import Breadcrumbs from '../../components/Breadcrumbs.vue';
import Skeleton from '../../components/Skeleton.vue';
import EmptyState from '../../components/EmptyState.vue';
import Pagination from '../../components/Pagination.vue';

const toast = useToast();
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
  axios.post(`/admin/research/${id}/approve`).then(() => {
    toast.success('Research approved.');
    fetch(list.value.current_page);
  }).catch(() => toast.error('Failed to approve research.'));
}

function reject(id) {
  axios.post(`/admin/research/${id}/reject`).then(() => {
    toast.success('Research rejected.');
    fetch(list.value.current_page);
  }).catch(() => toast.error('Failed to reject research.'));
}

onMounted(() => fetch());
</script>
