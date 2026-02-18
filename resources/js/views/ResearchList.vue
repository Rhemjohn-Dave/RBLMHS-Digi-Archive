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

    <!-- Recently viewed -->
    <div v-if="recentlyViewed.length" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <h2 class="mb-3 text-sm font-semibold text-slate-700">Recently viewed</h2>
      <div class="flex gap-3 overflow-x-auto pb-1 -mx-1 px-1">
        <router-link
          v-for="r in recentlyViewed"
          :key="r.id"
          :to="'/research/' + r.id"
          class="flex min-w-[200px] shrink-0 items-center gap-3 rounded-lg border border-slate-200 bg-slate-50/80 p-3 transition-colors hover:border-slate-300 hover:bg-slate-100"
        >
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-red-100 text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2 5 5h-5V4zM8 16v-2h5v2H8zm0-4v-2h8v2H8zm0-4v-2h8v2H8z"/></svg>
          </span>
          <span class="min-w-0 truncate text-sm font-medium text-slate-800">{{ r.title }}</span>
        </router-link>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
          <input
            v-model="search"
            type="search"
            placeholder="Search title, authors..."
            class="input-base max-w-xs text-sm"
          />
          <input
            v-model="year"
            type="number"
            placeholder="Year"
            class="input-base w-28 text-sm"
            min="1900"
            :max="new Date().getFullYear()"
          />
        </div>
        <div class="flex items-center gap-2">
          <button type="button" @click="fetch" class="btn-primary text-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            Search
          </button>
          <button
            v-if="search || year"
            type="button"
            @click="clearFilters"
            class="btn-secondary text-sm"
          >
            Clear
          </button>
        </div>
      </div>
      <!-- Filter chips -->
      <div v-if="search || year" class="mt-3 flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-500">Active filters:</span>
        <span v-if="search" class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">
          Search: {{ search }}
          <button type="button" @click="search = ''; fetch(1);" class="rounded-full p-0.5 hover:bg-slate-300" aria-label="Remove search">×</button>
        </span>
        <span v-if="year" class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">
          Year: {{ year }}
          <button type="button" @click="year = ''; fetch(1);" class="rounded-full p-0.5 hover:bg-slate-300" aria-label="Remove year">×</button>
        </span>
      </div>
    </div>

    <!-- View toggle + results -->
    <div v-if="!loading && list.data?.length" class="flex items-center justify-between">
      <p class="text-sm text-slate-500">{{ list.total || list.data.length }} result(s)</p>
      <div class="flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm">
        <button
          type="button"
          @click="viewMode = 'list'"
          class="rounded-md p-2 transition-colors"
          :class="viewMode === 'list' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100'"
          title="List view"
          aria-label="List view"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
        </button>
        <button
          type="button"
          @click="viewMode = 'grid'"
          class="rounded-md p-2 transition-colors"
          :class="viewMode === 'grid' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100'"
          title="Grid view"
          aria-label="Grid view"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
        </button>
      </div>
    </div>

    <!-- States & results -->
    <div v-if="loading" class="space-y-3">
      <div
        v-for="i in 5"
        :key="i"
        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
      >
        <Skeleton variant="title" class="mb-2" />
        <Skeleton variant="text" class="!w-2/3" />
        <div class="mt-3 flex gap-2">
          <Skeleton class="h-6 w-24 rounded-full" />
        </div>
      </div>
    </div>
    <EmptyState
      v-else-if="!list.data?.length"
      :title="search || year ? 'No research found' : 'No research yet'"
      :description="search || year ? 'Try adjusting your search or filters.' : 'Approved research will appear here.'"
    />
    <!-- List view -->
    <ul v-else-if="viewMode === 'list'" class="space-y-3">
      <li
        v-for="r in list.data"
        :key="r.id"
        class="group card transition hover:border-slate-300 hover:shadow-md"
      >
        <router-link :to="'/research/' + r.id" class="flex gap-3 p-4">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600" aria-hidden="true">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2 5 5h-5V4zM8 16v-2h5v2H8zm0-4v-2h8v2H8zm0-4v-2h8v2H8z"/></svg>
          </span>
          <div class="min-w-0 flex-1 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <h2 class="text-base font-semibold text-slate-900 group-hover:underline">{{ r.title }}</h2>
              <p class="mt-1 text-sm text-slate-600"><span class="font-medium text-slate-700">Authors:</span> {{ r.authors || 'N/A' }}</p>
            </div>
            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 sm:mt-0">
              <span v-if="r.published_date" class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">{{ formatDate(r.published_date) }}</span>
              <span v-if="(r.download_count ?? 0) > 0" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700" :title="r.download_count + ' download(s)'">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                {{ r.download_count }}
              </span>
            </div>
          </div>
        </router-link>
      </li>
    </ul>
    <!-- Grid view -->
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <router-link
        v-for="r in list.data"
        :key="r.id"
        :to="'/research/' + r.id"
        class="group card flex flex-col p-4 transition hover:border-slate-300 hover:shadow-md"
      >
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600" aria-hidden="true">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2 5 5h-5V4zM8 16v-2h5v2H8zm0-4v-2h8v2H8zm0-4v-2h8v2H8z"/></svg>
        </span>
        <h2 class="mt-3 line-clamp-2 text-sm font-semibold text-slate-900 group-hover:underline">{{ r.title }}</h2>
        <p class="mt-1 line-clamp-1 text-xs text-slate-600">{{ r.authors || 'N/A' }}</p>
        <div class="mt-auto flex flex-wrap gap-2 pt-3 text-xs text-slate-500">
          <span v-if="r.published_date" class="rounded-full bg-slate-100 px-2 py-0.5">{{ formatDate(r.published_date) }}</span>
          <span v-if="(r.download_count ?? 0) > 0" class="rounded-full bg-slate-100 px-2 py-0.5">{{ r.download_count }} downloads</span>
        </div>
      </router-link>
    </div>

    <Pagination
      v-if="list.data?.length"
      :current-page="list.current_page"
      :last-page="list.last_page"
      @page="go"
      class="mt-4"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRecentlyViewed } from '../composables/useRecentlyViewed';
import Skeleton from '../components/Skeleton.vue';
import EmptyState from '../components/EmptyState.vue';
import Pagination from '../components/Pagination.vue';

const { getList: getRecentlyViewed } = useRecentlyViewed();
const recentlyViewed = ref([]);

const search = ref('');
const year = ref('');
const loading = ref(true);
const viewMode = ref('list');
const list = ref({ data: [], current_page: 1, last_page: 1, total: 0 });

function clearFilters() {
  search.value = '';
  year.value = '';
  fetch(1);
}

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

onMounted(() => {
  recentlyViewed.value = getRecentlyViewed();
  fetch();
});
</script>
