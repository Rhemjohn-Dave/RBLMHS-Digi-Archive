<template>
  <div class="space-y-4">
    <!-- Page header -->
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

    <!-- Loading / empty states -->
    <div
      v-if="loading"
      class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 py-10 text-center text-slate-500"
    >
      Loading pending research…
    </div>
    <div
      v-else-if="!list.data?.length"
      class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500"
    >
      <p class="text-sm">
        No pending research at the moment.
      </p>
    </div>

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
          <div class="flex items-center gap-2 sm:flex-col sm:items-end sm:gap-1">
            <button
              type="button"
              @click="approve(r.id)"
              class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs sm:text-sm font-medium text-white hover:bg-emerald-500"
            >
              Approve
            </button>
            <button
              type="button"
              @click="reject(r.id)"
              class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs sm:text-sm font-medium text-white hover:bg-red-500"
            >
              Reject
            </button>
          </div>
        </div>
      </li>
    </ul>

    <!-- Pagination -->
    <div
      v-if="list.data?.length && list.last_page > 1"
      class="mt-4 flex flex-wrap items-center justify-center gap-3 text-sm text-slate-600"
    >
      <button
        :disabled="list.current_page === 1"
        @click="fetch(list.current_page - 1)"
        class="rounded-full border border-slate-300 px-3 py-1.5 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
      >
        Prev
      </button>
      <span class="px-2">
        Page
        <span class="font-semibold text-slate-900">{{ list.current_page }}</span>
        of
        <span class="font-semibold text-slate-900">{{ list.last_page }}</span>
      </span>
      <button
        :disabled="list.current_page === list.last_page"
        @click="fetch(list.current_page + 1)"
        class="rounded-full border border-slate-300 px-3 py-1.5 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50"
      >
        Next
      </button>
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
