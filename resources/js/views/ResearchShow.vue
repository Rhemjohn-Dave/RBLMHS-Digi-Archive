<template>
  <div v-if="research" class="max-w-3xl space-y-4">
    <!-- Back link -->
    <router-link
      to="/research"
      class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900"
    >
      <span class="mr-1 text-base">←</span>
      Back to research list
    </router-link>

    <!-- Main card -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <!-- Title -->
      <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
        {{ research.title }}
      </h1>

      <!-- Meta info -->
      <div class="mt-4 space-y-1 text-sm text-slate-600">
        <p>
          <span class="font-semibold text-slate-800">Authors: </span>
          <span>{{ research.authors || 'N/A' }}</span>
        </p>
        <p v-if="research.co_authors">
          <span class="font-semibold text-slate-800">Co-authors: </span>
          <span>{{ research.co_authors }}</span>
        </p>
        <p v-if="research.adviser">
          <span class="font-semibold text-slate-800">Adviser: </span>
          <span>{{ research.adviser }}</span>
        </p>
        <p v-if="research.published_date">
          <span class="font-semibold text-slate-800">Published: </span>
          <span>{{ formatDate(research.published_date) }}</span>
        </p>
      </div>

      <!-- Abstract -->
      <div
        v-if="research.abstract"
        class="mt-6 rounded-lg bg-slate-50 p-4"
      >
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
          Abstract
        </h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-800 whitespace-pre-line">
          {{ research.abstract }}
        </p>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex flex-wrap items-center gap-3">
        <button
          v-if="canDownload"
          type="button"
          @click="download"
          :disabled="downloading"
          class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
        >
          {{ downloading ? 'Downloading…' : 'Download PDF' }}
        </button>
        <button
          v-else-if="user?.role === 'student'"
          type="button"
          @click="requestDownload"
          :disabled="requesting"
          class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-500 disabled:opacity-50"
        >
          {{ requesting ? 'Requesting…' : 'Request Download Permission' }}
        </button>
        <p
          v-else
          class="text-sm text-slate-500"
        >
          You need download permission to access this file.
        </p>
      </div>
    </div>
  </div>
  <div
    v-else
    class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 py-10 text-center text-slate-500"
  >
    Loading research…
  </div>
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
