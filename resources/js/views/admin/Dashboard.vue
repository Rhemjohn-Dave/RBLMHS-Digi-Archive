<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Admin Dashboard</h1>
    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>
    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-sm text-slate-500">Total Users</p>
          <p class="text-2xl font-semibold text-slate-800">{{ stats.total_users }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-sm text-slate-500">Pending Users</p>
          <p class="text-2xl font-semibold text-amber-600">{{ stats.pending_users }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-sm text-slate-500">Total Research</p>
          <p class="text-2xl font-semibold text-slate-800">{{ stats.total_research }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-sm text-slate-500">Pending Research</p>
          <p class="text-2xl font-semibold text-amber-600">{{ stats.pending_research }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-sm text-slate-500">Total Downloads</p>
          <p class="text-2xl font-semibold text-slate-800">{{ stats.total_downloads }}</p>
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
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="mb-4 font-medium text-slate-800">Downloads per Month</h2>
          <ul v-if="stats.downloads_per_month?.length" class="space-y-2 text-sm">
            <li v-for="m in stats.downloads_per_month" :key="m.year + '-' + m.month" class="flex justify-between">
              <span>{{ m.year }}-{{ String(m.month).padStart(2, '0') }}</span>
              <span>{{ m.count }}</span>
            </li>
          </ul>
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
        <div v-if="publishedLoading" class="py-6 text-center text-sm text-slate-500">Loading...</div>
        <div v-else-if="!publishedList.data?.length" class="py-6 text-center text-sm text-slate-500">No published research.</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
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
                  <button type="button" @click="openEdit(r)" class="rounded bg-slate-700 px-2 py-1 text-xs text-white hover:bg-slate-600">Edit</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="publishedList.data?.length && publishedList.last_page > 1" class="mt-3 flex justify-center gap-2">
          <button :disabled="publishedList.current_page === 1" @click="fetchPublished(publishedList.current_page - 1)" class="rounded border border-slate-300 px-2 py-1 text-sm disabled:opacity-50">Prev</button>
          <span class="py-1 text-sm">Page {{ publishedList.current_page }} of {{ publishedList.last_page }}</span>
          <button :disabled="publishedList.current_page === publishedList.last_page" @click="fetchPublished(publishedList.current_page + 1)" class="rounded border border-slate-300 px-2 py-1 text-sm disabled:opacity-50">Next</button>
        </div>
      </div>
    </template>

    <!-- Edit modal -->
    <Teleport to="body">
      <div v-if="editing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="editing = null">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-slate-200 bg-white p-6 shadow-lg" @click.stop>
          <h3 class="mb-4 text-lg font-semibold text-slate-800">Edit Research</h3>
          <form @submit.prevent="saveEdit" class="space-y-3">
            <div>
              <label class="mb-0.5 block text-sm font-medium text-slate-600">Title</label>
              <input v-model="editForm.title" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
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
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

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
