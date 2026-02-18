<template>
  <div class="flex min-h-screen flex-col bg-slate-50 text-slate-800">
    <!-- Top bar -->
    <header v-if="user" class="sticky top-0 z-20 bg-slate-800 text-white shadow-sm border-b border-slate-700/60">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-2 px-3 sm:h-16 sm:px-6">
        <router-link
          to="/"
          class="flex min-w-0 shrink items-center gap-2 text-lg font-semibold tracking-tight"
        >
          <img
            src="/rbllogo.jpg"
            alt="RBLMHS Digi Archive"
            class="h-8 w-auto shrink-0 object-contain sm:h-10"
          />
          <span class="hidden truncate sm:inline md:max-w-[200px]">RBLMHS Digi Archive</span>
        </router-link>
        <div class="flex shrink-0 items-center gap-1 sm:gap-3">
          <!-- Notifications: wrapper has click-outside so bell click is not treated as outside -->
          <div class="relative" v-click-outside="closeNotifications">
            <button
              type="button"
              @click.stop="toggleNotifications"
              class="relative z-10 rounded-full p-2 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors"
              aria-label="Notifications"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white"
              >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
              </span>
            </button>
            <div
              v-if="showNotifications"
              class="absolute right-0 top-full z-50 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-xl"
              role="dialog"
              aria-label="Notifications"
            >
              <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <span class="text-sm font-semibold text-slate-800">Notifications</span>
                <button
                  v-if="unreadCount > 0"
                  type="button"
                  @click="markAllAsRead"
                  class="text-xs font-medium text-slate-600 hover:text-slate-800 transition-colors"
                >
                  Mark all read
                </button>
              </div>
              <div class="max-h-96 overflow-y-auto">
                <template v-if="notifications.length">
                  <router-link
                    v-for="n in notifications"
                    :key="n.id"
                    :to="notificationRoute(n)"
                    @click.stop="handleNotificationClick(n)"
                    class="block w-full cursor-pointer border-b border-slate-50 px-4 py-3 text-left no-underline transition-colors hover:bg-slate-50 active:bg-slate-100 last:border-0"
                    :class="{ 'bg-slate-50/80 hover:bg-slate-100': !n.read_at }"
                  >
                    <p class="text-sm font-medium text-slate-800">{{ n.title }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-600">{{ n.message }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ formatTime(n.created_at) }}</p>
                  </router-link>
                </template>
                <div v-else class="flex flex-col items-center justify-center px-4 py-10 text-center">
                  <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                  </span>
                  <p class="mt-3 text-sm font-medium text-slate-600">No notifications yet</p>
                  <p class="mt-0.5 text-xs text-slate-500">You’ll see updates here when something happens.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-1.5 rounded-full bg-slate-700/70 pl-2 pr-1.5 py-1.5 text-xs sm:pl-3 sm:pr-2 sm:py-1.5 sm:text-sm">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-600 text-xs font-bold text-white sm:hidden">{{ (user?.username || 'U').charAt(0).toUpperCase() }}</span>
            <div class="hidden min-w-0 flex-col leading-tight sm:flex">
              <span class="truncate font-semibold text-slate-50 max-w-[100px] md:max-w-none">
                {{ user?.username }}
              </span>
              <span class="text-slate-300 capitalize text-[10px] sm:text-xs">
                {{ user?.role }}
              </span>
            </div>
            <button
              type="button"
              @click="logout"
              class="rounded-full bg-slate-900/70 px-2 py-1 text-[11px] font-medium text-slate-50 hover:bg-slate-900 transition-colors sm:px-2.5 sm:text-xs"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content (grows to push footer down) -->
    <div class="flex flex-1 flex-col">
    <div
      v-if="user?.role === 'admin'"
      class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:flex lg:gap-6"
    >
      <!-- Mobile overlay -->
      <div
        v-if="adminSidebarOpen"
        class="fixed inset-0 z-30 bg-black/50 lg:hidden"
        aria-hidden="true"
        @click="adminSidebarOpen = false"
      />

      <!-- Admin sidebar: drawer on mobile, inline on lg+ -->
      <aside
        class="fixed inset-y-0 left-0 z-40 w-56 transform rounded-none border-0 bg-white shadow-lg transition-transform duration-200 ease-out lg:relative lg:inset-auto lg:shrink-0 lg:translate-x-0 lg:rounded-xl lg:border lg:border-slate-200"
        :class="adminSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        aria-label="Admin menu"
      >
        <div class="flex h-full flex-col">
          <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 lg:hidden">
            <span class="text-sm font-semibold text-slate-800">Menu</span>
            <button
              type="button"
              @click="adminSidebarOpen = false"
              class="rounded-full p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
              aria-label="Close menu"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
          <nav class="py-4">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
              Admin Menu
            </p>
            <router-link
              to="/admin"
              @click="adminSidebarOpen = false"
              class="flex items-center gap-2 px-4 py-2 text-sm rounded-r-full border-l-2 border-transparent text-slate-700 hover:bg-slate-100"
              :class="route.path === '/admin' ? 'bg-slate-100 border-slate-800 font-semibold text-slate-900' : ''"
            >
              <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
              Dashboard
            </router-link>
            <router-link
              to="/admin/users"
              @click="adminSidebarOpen = false"
              class="flex items-center gap-2 px-4 py-2 text-sm rounded-r-full border-l-2 border-transparent text-slate-700 hover:bg-slate-100"
              :class="route.path.startsWith('/admin/users') ? 'bg-slate-100 border-slate-800 font-semibold text-slate-900' : ''"
            >
              <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
              Users
            </router-link>
            <router-link
              to="/admin/research"
              @click="adminSidebarOpen = false"
              class="flex items-center gap-2 px-4 py-2 text-sm rounded-r-full border-l-2 border-transparent text-slate-700 hover:bg-slate-100"
              :class="route.path.startsWith('/admin/research') ? 'bg-slate-100 border-slate-800 font-semibold text-slate-900' : ''"
            >
              <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
              Research
            </router-link>
            <router-link
              to="/admin/download-requests"
              @click="adminSidebarOpen = false"
              class="flex items-center gap-2 px-4 py-2 text-sm rounded-r-full border-l-2 border-transparent text-slate-700 hover:bg-slate-100"
              :class="route.path.startsWith('/admin/download-requests') ? 'bg-slate-100 border-slate-800 font-semibold text-slate-900' : ''"
            >
              <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
              Download Requests
            </router-link>
            <router-link
              to="/admin/download-logs"
              @click="adminSidebarOpen = false"
              class="flex items-center gap-2 px-4 py-2 text-sm rounded-r-full border-l-2 border-transparent text-slate-700 hover:bg-slate-100"
              :class="route.path.startsWith('/admin/download-logs') ? 'bg-slate-100 border-slate-800 font-semibold text-slate-900' : ''"
            >
              <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
              Download Logs
            </router-link>
          </nav>
        </div>
      </aside>

      <!-- Admin content -->
      <main class="min-w-0 flex-1">
        <button
          type="button"
          @click="adminSidebarOpen = true"
          class="mb-4 flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 lg:hidden"
          aria-label="Open admin menu"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
          Menu
        </button>
        <router-view />
      </main>
    </div>

    <!-- Non-admin layout -->
    <main
      v-else
      class="mx-auto max-w-7xl px-4 py-8 sm:px-6"
    >
      <div
        v-if="user && user.status !== 'approved' && user.role !== 'admin'"
        class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800"
      >
        Your account is pending approval. You will have full access once an administrator approves your registration.
      </div>
      <nav
        v-if="user"
        class="mb-4 flex flex-wrap gap-2 rounded-lg bg-white p-2.5 sm:p-3 text-sm shadow-sm border border-slate-200"
      >
        <router-link
          to="/research"
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 hover:bg-slate-100 transition-colors"
          :class="route.path.startsWith('/research') ? 'bg-slate-900 text-white font-medium' : 'text-slate-700'"
        >
          <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
          Research
        </router-link>
        <router-link
          v-if="user?.role === 'faculty'"
          to="/my-submissions"
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 hover:bg-slate-100 transition-colors"
          :class="route.path.startsWith('/my-submissions') ? 'bg-slate-900 text-white font-medium' : 'text-slate-700'"
        >
          <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
          My Submissions
        </router-link>
        <router-link
          v-if="user?.role === 'faculty'"
          to="/upload"
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 hover:bg-slate-100 transition-colors"
          :class="route.path.startsWith('/upload') ? 'bg-slate-900 text-white font-medium' : 'text-slate-700'"
        >
          <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
          Upload
        </router-link>
      </nav>
      <router-view />
    </main>
    </div>

    <AppFooter />
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { user, setUser, clearUser } from './auth';
import { useNotifications } from './composables/useNotifications';
import ToastContainer from './components/ToastContainer.vue';
import AppFooter from './components/AppFooter.vue';

const router = useRouter();
const route = useRoute();
const showNotifications = ref(false);
const adminSidebarOpen = ref(false);

const {
  notifications,
  unreadCount,
  fetchNotifications,
  fetchUnreadCount,
  markAsRead,
  markAllAsRead,
  goToNotification,
  startListening,
  stopListening,
} = useNotifications();

let dropdownPollInterval = null;

function toggleNotifications() {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    fetchNotifications();
    dropdownPollInterval = setInterval(() => fetchNotifications(), 15000);
  } else {
    if (dropdownPollInterval) {
      clearInterval(dropdownPollInterval);
      dropdownPollInterval = null;
    }
  }
}

function closeNotifications() {
  showNotifications.value = false;
  if (dropdownPollInterval) {
    clearInterval(dropdownPollInterval);
    dropdownPollInterval = null;
  }
}

function notificationRoute(n) {
  const path = n?.link?.trim();
  return path && path !== '#' ? path : '/';
}

function handleNotificationClick(n) {
  if (!n?.read_at) markAsRead(n.id);
  closeNotifications();
}

function formatTime(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  const now = new Date();
  const diff = (now - d) / 60000;
  if (diff < 1) return 'Just now';
  if (diff < 60) return `${Math.floor(diff)}m ago`;
  if (diff < 1440) return `${Math.floor(diff / 60)}h ago`;
  if (diff < 10080) return `${Math.floor(diff / 1440)}d ago`;
  return d.toLocaleDateString();
}

onMounted(() => {
  if (localStorage.getItem('token') && !user.value) {
    axios.get('/user').then(({ data }) => {
      setUser(data);
      startListening(data.id);
    });
  } else if (user.value?.id) {
    startListening(user.value.id);
  }
});

watch(user, (val) => {
  if (val?.id) startListening(val.id);
});

function logout() {
  stopListening();
  axios.post('/logout').catch(() => {});
  localStorage.removeItem('token');
  clearUser();
  router.push({ name: 'login' });
}
</script>
