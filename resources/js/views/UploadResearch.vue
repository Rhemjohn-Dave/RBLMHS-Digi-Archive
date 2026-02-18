<template>
  <div class="max-w-2xl space-y-6">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Upload Research</h1>
      <p class="mt-1 text-sm text-slate-500">Submit your research for admin approval.</p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Basic info -->
      <div class="card overflow-hidden">
        <div class="card-header border-l-4 border-slate-800 bg-slate-50">Basic information</div>
        <div class="space-y-4 p-5">
          <div>
            <label class="label-base" for="up-title">Title *</label>
            <input id="up-title" v-model="form.title" type="text" required class="input-base" :class="{ 'input-error': errors.title }" />
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
          </div>
          <div>
            <label class="label-base" for="up-authors">Authors *</label>
            <input id="up-authors" v-model="form.authors" type="text" required class="input-base" :class="{ 'input-error': errors.authors }" />
            <p v-if="errors.authors" class="mt-1 text-sm text-red-600">{{ errors.authors }}</p>
          </div>
          <div>
            <label class="label-base" for="up-co_authors">Co-authors</label>
            <input id="up-co_authors" v-model="form.co_authors" type="text" class="input-base" />
          </div>
          <div>
            <label class="label-base" for="up-adviser">Adviser</label>
            <input id="up-adviser" v-model="form.adviser" type="text" class="input-base" />
          </div>
          <div>
            <label class="label-base" for="up-published_date">Published date</label>
            <input id="up-published_date" v-model="form.published_date" type="date" class="input-base" />
          </div>
        </div>
      </div>

      <!-- Abstract -->
      <div class="card overflow-hidden">
        <div class="card-header border-l-4 border-slate-800 bg-slate-50">Abstract</div>
        <div class="p-5">
          <label class="label-base" for="up-abstract">Abstract</label>
          <textarea id="up-abstract" v-model="form.abstract" rows="4" class="input-base resize-y min-h-[100px]"></textarea>
        </div>
      </div>

      <!-- File -->
      <div class="card overflow-hidden">
        <div class="card-header border-l-4 border-slate-800 bg-slate-50">PDF file</div>
        <div class="p-5">
          <label class="label-base" for="up-file">PDF file (max 20MB) *</label>
          <input
            id="up-file"
            ref="fileInput"
            type="file"
            accept=".pdf"
            required
            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
          />
          <p v-if="errors.file" class="mt-1 text-sm text-red-600">{{ errors.file }}</p>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <button type="submit" :disabled="loading" class="btn-primary">
          <span v-if="loading" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" aria-hidden="true" />
          {{ loading ? 'Uploading…' : 'Submit for approval' }}
        </button>
        <router-link to="/my-submissions" class="btn-secondary">Cancel</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from '../composables/useToast';

const router = useRouter();
const toast = useToast();
const fileInput = ref(null);
const loading = ref(false);
const MAX_FILE_SIZE_BYTES = 20 * 1024 * 1024;
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
    await axios.post('/research', fd);
    toast.success('Research submitted for approval.');
    router.push('/my-submissions');
  } catch (e) {
    const resp = e.response?.data;
    const d = resp?.errors || {};
    Object.entries(d).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v));
    if (!errors.file && resp?.message) errors.file = resp.message;
    if (!Object.keys(d).length && !errors.file) errors.file = 'Upload failed. Please try again.';
    toast.error(errors.file || errors.title || 'Upload failed. Please try again.');
  } finally {
    loading.value = false;
  }
}
</script>
