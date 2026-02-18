<template>
  <div v-if="research" class="max-w-3xl space-y-6">
    <Breadcrumbs :items="[{ label: 'Research', to: '/research' }, { label: research.title, to: '' }]" />
    <router-link
      to="/research"
      class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50 transition-colors"
    >
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
      Back to research list
    </router-link>

    <!-- Title card -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-semibold leading-tight text-slate-900 sm:text-3xl tracking-tight">
        {{ research.title }}
      </h1>
      <div v-if="research.published_date || (research.download_count ?? 0) > 0" class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
        <span v-if="research.published_date" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          {{ formatDate(research.published_date) }}
        </span>
        <span v-if="(research.download_count ?? 0) > 0" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
          {{ research.download_count }} download{{ research.download_count !== 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    <!-- Metadata card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">Details</h2>
      <dl class="grid gap-3 sm:grid-cols-1">
        <div>
          <dt class="text-xs font-medium text-slate-500">Authors</dt>
          <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ research.authors || 'N/A' }}</dd>
        </div>
        <div v-if="research.co_authors">
          <dt class="text-xs font-medium text-slate-500">Co-authors</dt>
          <dd class="mt-0.5 text-sm text-slate-800">{{ research.co_authors }}</dd>
        </div>
        <div v-if="research.adviser">
          <dt class="text-xs font-medium text-slate-500">Adviser</dt>
          <dd class="mt-0.5 text-sm text-slate-800">{{ research.adviser }}</dd>
        </div>
      </dl>
    </div>

    <!-- Abstract card -->
    <div
      v-if="research.abstract"
      class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden"
    >
      <div class="border-l-4 border-slate-800 bg-slate-50 px-5 py-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
          Abstract
        </h2>
      </div>
      <div class="p-5">
        <p class="text-sm leading-relaxed text-slate-700 whitespace-pre-line">
          {{ research.abstract }}
        </p>
      </div>
    </div>

    <!-- Actions card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Download</h2>
      <div class="flex flex-wrap items-center gap-3">
        <button
          v-if="canDownload"
          type="button"
          @click="download"
          :disabled="downloading"
          class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800 disabled:opacity-50 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
          {{ downloading ? 'Downloading…' : 'Download PDF' }}
        </button>
        <button
          v-else-if="user?.role === 'student'"
          type="button"
          @click="requestDownload"
          :disabled="requesting"
          class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-amber-500 disabled:opacity-50 transition-colors"
        >
          {{ requesting ? 'Requesting…' : 'Request Download Permission' }}
        </button>
        <p v-else class="text-sm text-slate-500">
          You need download permission to access this file.
        </p>
        <button
          type="button"
          @click="share"
          class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
          Share
        </button>
        <button
          type="button"
          @click="showCitePanel = !showCitePanel"
          class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
          Cite this
        </button>
      </div>

      <!-- Citation panel (APA) -->
      <div v-if="showCitePanel" class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">APA 7 citation</p>
        <p class="text-sm leading-relaxed text-slate-800 font-serif">{{ apaCitation }}</p>
        <button
          type="button"
          @click="copyCitation"
          class="mt-3 inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700 transition-colors"
        >
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
          {{ citationCopied ? 'Copied!' : 'Copy citation' }}
        </button>
      </div>
    </div>

    <!-- Recently viewed (others) -->
    <div v-if="otherRecentlyViewed.length" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Recently viewed</h2>
      <div class="flex flex-wrap gap-2">
        <router-link
          v-for="r in otherRecentlyViewed"
          :key="r.id"
          :to="'/research/' + r.id"
          class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-100"
        >
          <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-red-100 text-red-600">
            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2 5 5h-5V4zM8 16v-2h5v2H8zm0-4v-2h8v2H8zm0-4v-2h8v2H8z"/></svg>
          </span>
          <span class="truncate max-w-[180px] sm:max-w-xs">{{ r.title }}</span>
        </router-link>
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
import { useToast } from '../composables/useToast';
import { useRecentlyViewed } from '../composables/useRecentlyViewed';
import Breadcrumbs from '../components/Breadcrumbs.vue';

const route = useRoute();
const toast = useToast();
const { add: addRecentlyViewed, getList: getRecentlyViewed } = useRecentlyViewed();
const research = ref(null);
const otherRecentlyViewed = ref([]);
const downloading = ref(false);
const requesting = ref(false);
const showCitePanel = ref(false);
const citationCopied = ref(false);

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

const apaCitation = computed(() => {
  if (!research.value) return '';
  const r = research.value;
  const authors = (r.authors || 'Unknown').trim();
  const year = r.published_date ? new Date(r.published_date).getFullYear() : 'n.d.';
  const title = (r.title || 'Untitled').trim();
  const url = typeof window !== 'undefined' ? window.location.href : '';
  return `${authors}. (${year}). ${title}. RBLMHS Digi Archive, Rafael B. Lacson Memorial High School. ${url}`;
});

function share() {
  const url = window.location.href;
  const title = research.value?.title || 'Research';
  const text = research.value?.authors ? `${research.value.title} by ${research.value.authors}` : research.value?.title;
  if (navigator.share) {
    navigator.share({ title, text, url }).then(() => {
      toast.success('Link shared.');
    }).catch((err) => {
      if (err.name !== 'AbortError') {
        copyToClipboard(url);
        toast.success('Link copied to clipboard.');
      }
    });
  } else {
    copyToClipboard(url);
    toast.success('Link copied to clipboard.');
  }
}

function copyToClipboard(text) {
  if (navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(text);
  } else {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'absolute';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  }
}

function copyCitation() {
  copyToClipboard(apaCitation.value);
  citationCopied.value = true;
  toast.success('Citation copied to clipboard.');
  setTimeout(() => { citationCopied.value = false; }, 2000);
}

onMounted(() => {
  axios.get(`/research/${route.params.id}`).then(({ data }) => {
    research.value = data;
    addRecentlyViewed({ id: data.id, title: data.title });
    const currentId = Number(route.params.id);
    otherRecentlyViewed.value = getRecentlyViewed().filter((r) => Number(r.id) !== currentId);
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
    toast.success('Download started.');
  }).catch(() => {
    toast.error('Download failed. Please try again.');
  }).finally(() => { downloading.value = false; });
}

function requestDownload() {
  requesting.value = true;
  axios.post(`/research/${route.params.id}/request-download`).then(() => {
    research.value.download_permission = 'pending';
    toast.success('Download permission requested. An admin will review it.');
  }).catch(() => {
    toast.error('Request failed. Please try again.');
  }).finally(() => { requesting.value = false; });
}
</script>
