<template>
  <div class="max-w-2xl">
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">Upload Research</h1>
    <form @submit.prevent="submit" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Title *</label>
        <input v-model="form.title" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" />
        <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Authors *</label>
        <input v-model="form.authors" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" />
        <p v-if="errors.authors" class="mt-1 text-sm text-red-600">{{ errors.authors }}</p>
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Co-authors</label>
        <input v-model="form.co_authors" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Adviser</label>
        <input v-model="form.adviser" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Published Date</label>
        <input v-model="form.published_date" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Abstract</label>
        <textarea v-model="form.abstract" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">PDF File (max 20MB) *</label>
        <input ref="fileInput" type="file" accept=".pdf" required class="w-full rounded-lg border border-slate-300 px-3 py-2" />
        <p v-if="errors.file" class="mt-1 text-sm text-red-600">{{ errors.file }}</p>
      </div>
      <button type="submit" :disabled="loading" class="rounded-lg bg-slate-800 px-6 py-2.5 font-medium text-white hover:bg-slate-700 disabled:opacity-50">
        {{ loading ? 'Uploading...' : 'Submit for Approval' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const fileInput = ref(null);
const loading = ref(false);
const MAX_FILE_SIZE_BYTES = 20 * 1024 * 1024; // 20MB
const form = reactive({
  title: '',
  authors: '',
  co_authors: '',
  adviser: '',
  published_date: '',
  abstract: '',
});
const errors = reactive({});

async function submit() {
  Object.keys(errors).forEach((k) => delete errors[k]);
  const file = fileInput.value?.files?.[0];
  if (!file) {
    errors.file = 'Please select a PDF file.';
    return;
  }
  if (file.type !== 'application/pdf' && !file.name?.toLowerCase().endsWith('.pdf')) {
    errors.file = 'Please select a valid PDF file.';
    return;
  }
  if (file.size > MAX_FILE_SIZE_BYTES) {
    errors.file = 'PDF file must be 20MB or smaller.';
    return;
  }
  loading.value = true;
  const fd = new FormData();
  fd.append('file', file);
  Object.entries(form).forEach(([k, v]) => { if (v) fd.append(k, v); });
  try {
    // Let the browser set the correct multipart boundary headers
    await axios.post('/research', fd);
    router.push('/my-submissions');
  } catch (e) {
    const resp = e.response?.data;
    const d = resp?.errors || {};
    Object.entries(d).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v));

    // When PHP rejects the upload (size limits / partial upload), Laravel returns:
    // { errors: { file: ["The file failed to upload."] } }
    if (!errors.file && resp?.message) {
      errors.file = resp.message;
    }
    if (!Object.keys(d).length && !errors.file) {
      errors.file = 'Upload failed. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
