<template>
  <div v-if="research" class="max-w-3xl">
    <router-link to="/research" class="mb-4 inline-block text-sm text-indigo-600 hover:underline">← Back to list</router-link>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-semibold text-slate-800">{{ research.title }}</h1>
      <p class="mt-2 text-slate-600"><strong>Authors:</strong> {{ research.authors }}</p>
      <p v-if="research.co_authors" class="text-slate-600"><strong>Co-authors:</strong> {{ research.co_authors }}</p>
      <p v-if="research.adviser" class="text-slate-600"><strong>Adviser:</strong> {{ research.adviser }}</p>
      <p v-if="research.published_date" class="text-slate-600"><strong>Published:</strong> {{ formatDate(research.published_date) }}</p>
      <p v-if="research.abstract" class="mt-4 text-slate-700">{{ research.abstract }}</p>
      <div class="mt-6 flex items-center gap-4">
        <button v-if="canDownload" type="button" @click="download" :disabled="downloading" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500 disabled:opacity-50">
          {{ downloading ? 'Downloading...' : 'Download PDF' }}
        </button>
        <button v-else-if="user?.role === 'student'" type="button" @click="requestDownload" :disabled="requesting" class="rounded-lg bg-amber-600 px-4 py-2 text-white hover:bg-amber-500 disabled:opacity-50">
          {{ requesting ? 'Requesting...' : 'Request Download Permission' }}
        </button>
        <p v-else class="text-slate-500">You need download permission to access this file.</p>
      </div>
    </div>
  </div>
  <div v-else class="py-8 text-center text-slate-500">Loading...</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { user } from '../auth';

const route = useRoute();
const research = ref(null);
const downloading = ref(false);
const requesting = ref(false);

const canDownload = computed(() => {
  if (!user.value || !research.value) return false;
  if (user.value.role === 'faculty') return true;
  if (user.value.role === 'student') return research.value.download_permission === true;
  return false;
});

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString();
}

onMounted(() => {
  axios.get(`/research/${route.params.id}`).then(({ data }) => {
    research.value = data;
  });
});

function download() {
  downloading.value = true;
  axios.get(`/research/${route.params.id}/download`, { responseType: 'blob' }).then(({ data, headers }) => {
    const url = URL.createObjectURL(data);
    const a = document.createElement('a');
    a.href = url;
    const name = (headers['content-disposition']?.match(/filename="?([^";]+)"?/)?.[1]) || 'research.pdf';
    a.download = name;
    a.click();
    URL.revokeObjectURL(url);
    research.value.download_count = (research.value.download_count || 0) + 1;
  }).finally(() => { downloading.value = false; });
}

function requestDownload() {
  requesting.value = true;
  axios.post(`/research/${route.params.id}/request-download`).then(() => {
    research.value.download_permission = 'pending';
  }).finally(() => { requesting.value = false; });
}
</script>
