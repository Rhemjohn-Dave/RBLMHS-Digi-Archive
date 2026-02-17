<template>
  <div class="min-h-screen bg-slate-50 text-slate-800">
    <!-- Top bar -->
    <header v-if="user" class="bg-slate-800 text-white shadow">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6">
        <router-link to="/" class="text-lg font-semibold tracking-tight">
          RBLMHS Digi Archive
        </router-link>
        <div class="flex items-center gap-4">
          <!-- Notifications: wrapper has click-outside so bell click is not treated as outside -->
          <div class="relative" v-click-outside="closeNotifications">
            <button
              type="button"
              @click.stop="toggleNotifications"
              class="relative z-10 rounded p-2 text-slate-300 hover:bg-slate-700 hover:text-white"
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
              class="absolute right-0 top-full z-50 mt-1 w-80 rounded-lg border border-slate-200 bg-white shadow-lg"
            >
              <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2">
                <span class="text-sm font-semibold text-slate-800">Latest notifications</span>
                <button
                  v-if="unreadCount > 0"
                  type="button"
                  @click="markAllAsRead"
                  class="text-xs text-indigo-600 hover:underline"
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
                    class="block w-full cursor-pointer border-b border-slate-100 px-4 py-3 text-left no-underline transition-colors hover:bg-slate-100 active:bg-slate-200 last:border-0"
                    :class="{ 'bg-indigo-50/70 hover:bg-indigo-100': !n.read_at }"
                  >
                    <p class="text-sm font-medium text-slate-800">{{ n.title }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-600">{{ n.message }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ formatTime(n.created_at) }}</p>
                  </router-link>
                </template>
                <p v-else class="px-4 py-6 text-center text-sm text-slate-500">No notifications yet.</p>
              </div>
            </div>
          </div>
          <span class="text-sm text-slate-300">{{ user?.username }} ({{ user?.role }})</span>
          <button
            type="button"
            @click="logout"
            class="rounded bg-slate-600 px-3 py-1.5 text-sm hover:bg-slate-500"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <!-- Layout -->
    <div
      v-if="user?.role === 'admin'"
      class="mx-auto flex max-w-7xl px-4 py-8 sm:px-6 gap-6"
    >
      <!-- Admin sidebar -->
      <aside class="w-56 shrink-0 rounded-xl bg-white border border-slate-200 shadow-sm">
        <nav class="py-4">
          <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
            Admin Menu
          </p>
          <router-link
            to="/admin"
            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
          >
            Dashboard
          </router-link>
          <router-link
            to="/admin/users"
            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
          >
            Users
          </router-link>
          <router-link
            to="/admin/research"
            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
          >
            Research
          </router-link>
          <router-link
            to="/admin/download-requests"
            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
          >
            Download Requests
          </router-link>
          <router-link
            to="/admin/download-logs"
            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
          >
            Download Logs
          </router-link>
        </nav>
      </aside>

      <!-- Admin content -->
      <main class="flex-1">
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
        class="mb-4 flex flex-wrap gap-2 rounded-lg bg-white p-3 text-sm shadow-sm border border-slate-200"
      >
        <router-link
          to="/research"
          class="rounded px-3 py-1.5 hover:bg-slate-100"
        >
          Research
        </router-link>
        <router-link
          v-if="user?.role === 'faculty'"
          to="/my-submissions"
          class="rounded px-3 py-1.5 hover:bg-slate-100"
        >
          My Submissions
        </router-link>
        <router-link
          v-if="user?.role === 'faculty'"
          to="/upload"
          class="rounded px-3 py-1.5 hover:bg-slate-100"
        >
          Upload
        </router-link>
      </nav>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { user, setUser, clearUser } from './auth';
import { useNotifications } from './composables/useNotifications';

const router = useRouter();
const showNotifications = ref(false);

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
