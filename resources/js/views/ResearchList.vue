<template>
  <div class="space-y-4">
    <!-- Page header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">
          Research Repository
        </h1>
        <p class="mt-1 text-sm text-slate-600">
          Browse approved research outputs from the RBLMHS community.
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
          <input
            v-model="search"
            type="search"
            placeholder="Search title, authors..."
            class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-800/70"
          />
          <input
            v-model="year"
            type="number"
            placeholder="Year"
            class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-800/70"
            min="1900"
            :max="new Date().getFullYear()"
          />
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="fetch"
            class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800"
          >
            Search
          </button>
          <button
            v-if="search || year"
            type="button"
            @click="
              search = '';
              year = '';
              fetch(1);
            "
            class="text-xs text-slate-500 hover:text-slate-700"
          >
            Clear filters
          </button>
        </div>
      </div>
    </div>

    <!-- States & results -->
    <div v-if="loading" class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 py-10 text-center text-slate-500">
      Loading research…
    </div>
    <div
      v-else-if="!list.data?.length"
      class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500"
    >
      <p class="text-sm">
        No research found.
        <span v-if="search || year">Try adjusting your search or filters.</span>
      </p>
    </div>
    <ul v-else class="space-y-3">
      <li
        v-for="r in list.data"
        :key="r.id"
        class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md"
      >
        <router-link :to="'/research/' + r.id" class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h2 class="text-base font-semibold text-slate-900 group-hover:underline">
              {{ r.title }}
            </h2>
            <p class="mt-1 text-sm text-slate-600">
              <span class="font-medium text-slate-700">Authors:</span>
              {{ r.authors || 'N/A' }}
            </p>
          </div>
          <div class="mt-1 flex items-center gap-2 text-xs text-slate-500 sm:mt-0">
            <span
              v-if="r.published_date"
              class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700"
            >
              {{ formatDate(r.published_date) }}
            </span>
          </div>
        </router-link>
      </li>
    </ul>

    <!-- Pagination -->
    <div
      v-if="list.data?.length && list.last_page > 1"
      class="mt-4 flex flex-wrap items-center justify-center gap-3 text-sm text-slate-600"
    >
      <button
        :disabled="list.current_page === 1"
        @click="go(list.current_page - 1)"
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
        @click="go(list.current_page + 1)"
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
