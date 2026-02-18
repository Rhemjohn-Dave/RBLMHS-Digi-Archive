<template>
  <div>
    <Breadcrumbs :items="[{ label: 'Admin', to: '/admin' }, { label: 'Dashboard', to: '' }]" class="mb-2" />
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Admin Dashboard</h1>
    <div v-if="loading" class="space-y-4">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div v-for="i in 5" :key="i" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <Skeleton variant="line" class="mb-2 h-4 w-20" />
          <Skeleton variant="title" class="h-8 w-16" />
        </div>
      </div>
      <div class="grid gap-6 lg:grid-cols-2">
        <Skeleton variant="card" class="h-48" />
        <Skeleton variant="card" class="h-48" />
      </div>
    </div>
    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="card flex flex-col p-4">
          <div class="flex items-center gap-2 text-slate-500">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </span>
            <span class="text-sm font-medium">Total Users</span>
          </div>
          <p class="mt-2 text-2xl font-semibold text-slate-800">{{ stats.total_users }}</p>
        </div>
        <div class="card flex flex-col p-4">
          <div class="flex items-center gap-2 text-amber-600">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </span>
            <span class="text-sm font-medium">Pending Users</span>
          </div>
          <p class="mt-2 text-2xl font-semibold text-amber-600">{{ stats.pending_users }}</p>
        </div>
        <div class="card flex flex-col p-4">
          <div class="flex items-center gap-2 text-slate-500">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </span>
            <span class="text-sm font-medium">Total Research</span>
          </div>
          <p class="mt-2 text-2xl font-semibold text-slate-800">{{ stats.total_research }}</p>
        </div>
        <div class="card flex flex-col p-4">
          <div class="flex items-center gap-2 text-amber-600">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            </span>
            <span class="text-sm font-medium">Pending Research</span>
          </div>
          <p class="mt-2 text-2xl font-semibold text-amber-600">{{ stats.pending_research }}</p>
        </div>
        <div class="card flex flex-col p-4">
          <div class="flex items-center gap-2 text-slate-500">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            </span>
            <span class="text-sm font-medium">Total Downloads</span>
          </div>
          <p class="mt-2 text-2xl font-semibold text-slate-800">{{ stats.total_downloads }}</p>
        </div>
      </div>
      <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="mb-4 font-medium text-slate-800">Most Downloaded Research</h2>
          <ul v-if="stats.most_downloaded?.length" class="space-y-2">
            <li v-for="(r, i) in stats.most_downloaded" :key="r.id" class="flex justify-between text-sm">
              <span class="truncate">{{ i + 1 }}. {{ r.title }}</span>
              <span class="text-slate-500">{{ r.download_count }} downloads</span>
            </li>
          </ul>
          <p v-else class="text-sm text-slate-500">No downloads yet.</p>
        </div>
        <div class="card p-4">
          <h2 class="mb-4 font-medium text-slate-800">Downloads per Month</h2>
          <div v-if="stats.downloads_per_month?.length" class="flex items-end justify-between gap-1 h-36">
            <div
              v-for="m in chartMonths"
              :key="m.year + '-' + m.month"
              class="flex flex-1 flex-col items-center justify-end gap-0.5"
              :title="m.year + '-' + String(m.month).padStart(2, '0') + ': ' + m.count"
            >
              <span class="text-[10px] font-medium text-slate-600">{{ m.count }}</span>
              <div
                class="w-full min-h-[6px] rounded-t bg-slate-700 transition-all"
                :style="{ height: barHeight(m) }"
              />
              <span class="text-[10px] text-slate-500">{{ String(m.month).padStart(2, '0') }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-slate-500">No data yet.</p>
        </div>
      </div>

      <!-- Published Research (admin can edit) -->
      <div class="mt-8 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
          <h2 class="font-medium text-slate-800">Published Research</h2>
          <input
            v-model="publishedSearch"
            type="search"
            placeholder="Search title, authors..."
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm w-56"
            @input="debouncedFetchPublished"
          />
        </div>
        <div v-if="publishedLoading" class="space-y-2 py-6">
          <div v-for="i in 5" :key="i" class="flex gap-4">
            <div class="h-4 w-1/4 animate-pulse rounded bg-slate-200" />
            <div class="h-4 flex-1 animate-pulse rounded bg-slate-200" />
            <div class="h-4 w-20 animate-pulse rounded bg-slate-200" />
          </div>
        </div>
        <div v-else-if="!publishedList.data?.length" class="py-6 text-center text-sm text-slate-500">No published research.</div>
        <div v-else class="overflow-x-auto -mx-1 px-1">
          <p class="mb-2 text-xs text-slate-500 sm:hidden">Scroll horizontally to see all columns.</p>
          <table class="min-w-[32rem] w-full text-sm">
            <thead>
              <tr class="border-b border-slate-200 text-left text-slate-600">
                <th class="pb-2 pr-2 font-medium">Title</th>
                <th class="pb-2 pr-2 font-medium">Authors</th>
                <th class="pb-2 pr-2 font-medium">Published</th>
                <th class="pb-2 pr-2 font-medium">Downloads</th>
                <th class="pb-2 pl-2 font-medium text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in publishedList.data" :key="r.id" class="border-b border-slate-100">
                <td class="py-2 pr-2 font-medium text-slate-800">{{ r.title }}</td>
                <td class="py-2 pr-2 text-slate-600">{{ r.authors }}</td>
                <td class="py-2 pr-2 text-slate-600">{{ formatDate(r.published_date) }}</td>
                <td class="py-2 pr-2 text-slate-600">{{ r.download_count ?? 0 }}</td>
                <td class="py-2 pl-2 text-right">
                  <button type="button" @click="openEdit(r)" class="inline-flex items-center gap-1 rounded bg-slate-700 px-2 py-1.5 text-xs text-white hover:bg-slate-600 transition-colors" title="Edit research" aria-label="Edit research">
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  Edit
                </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination
          v-if="publishedList.data?.length"
          :current-page="publishedList.current_page"
          :last-page="publishedList.last_page"
          @page="fetchPublished"
          class="mt-3"
        />
      </div>
    </template>

    <!-- Edit modal -->
    <Teleport to="body">
      <div
        v-if="editing"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="editing = null"
        @keydown.escape="editing = null"
      >
        <div
          ref="editModalRef"
          role="dialog"
          aria-modal="true"
          aria-labelledby="edit-research-title"
          class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-slate-200 bg-white p-6 shadow-lg"
          @click.stop
          @keydown.escape="editing = null"
          @keydown.tab="trapTab"
        >
          <h3 id="edit-research-title" class="mb-4 text-lg font-semibold text-slate-800">Edit Research</h3>
          <form @submit.prevent="saveEdit" class="space-y-3">
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600" for="edit-form-title">Title</label>
              <input
                id="edit-form-title"
                ref="editFormTitleInput"
                v-model="editForm.title"
                type="text"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600">Authors</label>
              <input v-model="editForm.authors" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600">Co-authors</label>
              <input v-model="editForm.co_authors" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600">Adviser</label>
              <input v-model="editForm.adviser" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600">Published date</label>
              <input v-model="editForm.published_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600">Abstract</label>
              <textarea v-model="editForm.abstract" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600">Replace PDF (optional)</label>
              <input type="file" accept=".pdf" class="w-full text-sm text-slate-600" @change="onEditFileChange" />
            </div>
            <p v-if="editError" class="text-sm text-red-600">{{ editError }}</p>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" @click="editing = null" class="rounded border border-slate-300 px-3 py-1.5 text-sm">Cancel</button>
              <button type="submit" :disabled="saving" class="rounded bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-700 disabled:opacity-50">{{ saving ? 'Saving...' : 'Save' }}</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import Breadcrumbs from '../../components/Breadcrumbs.vue';
import Skeleton from '../../components/Skeleton.vue';
import Pagination from '../../components/Pagination.vue';

const loading = ref(true);
const stats = ref({
  total_users: 0,
  pending_users: 0,
  total_research: 0,
  pending_research: 0,
  total_downloads: 0,
  downloads_per_month: [],
  most_downloaded: [],
});

const publishedList = ref({ data: [], current_page: 1, last_page: 1 });
const publishedLoading = ref(false);
const publishedSearch = ref('');
let searchTimeout = null;

const editing = ref(null);
const editForm = reactive({
  title: '',
  authors: '',
  co_authors: '',
  adviser: '',
  published_date: '',
  abstract: '',
});
let editFile = null;
const saving = ref(false);
const editError = ref('');
const editModalRef = ref(null);
const editFormTitleInput = ref(null);

const chartMonths = computed(() => {
  const arr = stats.value.downloads_per_month || [];
  return arr.slice(-12);
});
const chartMax = computed(() => {
  const arr = chartMonths.value;
  if (!arr.length) return 1;
  return Math.max(1, ...arr.map((m) => m.count));
});
function barHeight(m) {
  const pct = (m.count / chartMax.value) * 100;
  return `${Math.max(4, pct)}%`;
}

const FOCUSABLE = 'button:not([disabled]), [href], input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function getFocusables(el) {
  if (!el) return [];
  return Array.from(el.querySelectorAll(FOCUSABLE)).filter((node) => {
  const style = window.getComputedStyle(node);
  return style.visibility !== 'hidden' && style.display !== 'none';
  });
}

function trapTab(e) {
  const container = editModalRef.value;
  if (!container || e.key !== 'Tab') return;
  const focusables = getFocusables(container);
  if (focusables.length === 0) return;
  const first = focusables[0];
  const last = focusables[focusables.length - 1];
  if (e.shiftKey) {
    if (document.activeElement === first) {
      e.preventDefault();
      last.focus();
    }
  } else {
    if (document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }
}

watch(editing, (val) => {
  if (val) {
    nextTick(() => {
      editFormTitleInput.value?.focus();
    });
  }
});

onMounted(() => {
  axios.get('/admin/dashboard').then(({ data }) => {
    stats.value = data;
    loading.value = false;
  }).catch(() => { loading.value = false; });
  fetchPublished();
});

function fetchPublished(page = 1) {
  publishedLoading.value = true;
  axios.get('/admin/research-published', {
    params: { page, per_page: 10, search: publishedSearch.value || undefined },
  }).then(({ data }) => {
    publishedList.value = data;
    publishedLoading.value = false;
  }).catch(() => { publishedLoading.value = false; });
}

function debouncedFetchPublished() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchPublished(1), 300);
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString();
}

function openEdit(r) {
  editing.value = r;
  editForm.title = r.title ?? '';
  editForm.authors = r.authors ?? '';
  editForm.co_authors = r.co_authors ?? '';
  editForm.adviser = r.adviser ?? '';
  editForm.published_date = r.published_date ? r.published_date.slice(0, 10) : '';
  editForm.abstract = r.abstract ?? '';
  editFile = null;
  editError.value = '';
}

function onEditFileChange(e) {
  const f = e.target.files?.[0];
  editFile = f || null;
}

function saveEdit() {
  if (!editing.value) return;
  saving.value = true;
  editError.value = '';
  const formData = new FormData();
  formData.append('_method', 'PUT');
  formData.append('title', editForm.title);
  formData.append('authors', editForm.authors);
  formData.append('co_authors', editForm.co_authors);
  formData.append('adviser', editForm.adviser);
  formData.append('published_date', editForm.published_date || '');
  formData.append('abstract', editForm.abstract);
  if (editFile) formData.append('file', editFile);
  axios.post(`/admin/research/${editing.value.id}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }).then(({ data }) => {
    const idx = publishedList.value.data.findIndex((x) => x.id === data.id);
    if (idx !== -1) publishedList.value.data[idx] = { ...publishedList.value.data[idx], ...data };
    editing.value = null;
  }).catch((e) => {
    editError.value = e.response?.data?.message || e.response?.data?.errors?.title?.[0] || 'Failed to save.';
  }).finally(() => { saving.value = false; });
}
</script>
