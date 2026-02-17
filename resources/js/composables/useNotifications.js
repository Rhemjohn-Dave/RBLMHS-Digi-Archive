import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { initNotificationEcho, disconnectNotificationEcho } from '../echo';

const notifications = ref([]);
const unreadCount = ref(0);
let loaded = false;

export function useNotifications() {
  const router = useRouter();

  async function fetchNotifications() {
    try {
      const { data } = await axios.get('/notifications');
      notifications.value = data.notifications ?? [];
      unreadCount.value = data.unread_count ?? 0;
      loaded = true;
    } catch {
      notifications.value = [];
      unreadCount.value = 0;
    }
  }

  async function fetchUnreadCount() {
    try {
      const { data } = await axios.get('/notifications/unread-count');
      unreadCount.value = data.unread_count ?? 0;
    } catch {}
  }

  async function markAsRead(id) {
    try {
      await axios.post(`/notifications/${id}/read`);
      const n = notifications.value.find((x) => x.id === id);
      if (n) n.read_at = new Date().toISOString();
      if (unreadCount.value > 0) unreadCount.value--;
    } catch {}
  }

  async function markAllAsRead() {
    try {
      await axios.post('/notifications/read-all');
      notifications.value.forEach((n) => (n.read_at = n.read_at || new Date().toISOString()));
      unreadCount.value = 0;
    } catch {}
  }

  function goToNotification(notification) {
    if (notification?.link) {
      if (!notification.read_at) markAsRead(notification.id);
      router.push(notification.link);
    }
  }

  /** Start WebSocket listener for real-time notifications (replaces polling). */
  function startListening(userId) {
    if (!userId) return;
    initNotificationEcho(userId, () => {
      fetchNotifications();
    });
  }

  /** Stop WebSocket listener (e.g. on logout). */
  function stopListening() {
    disconnectNotificationEcho();
  }

  function onWindowFocus() {
    if (localStorage.getItem('token')) {
      fetchUnreadCount();
    }
  }

  onMounted(() => {
    if (localStorage.getItem('token')) {
      if (!loaded) fetchNotifications();
    }
    if (typeof window !== 'undefined') {
      window.addEventListener('focus', onWindowFocus);
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') onWindowFocus();
      });
    }
  });

  onUnmounted(() => {
    stopListening();
    if (typeof window !== 'undefined') {
      window.removeEventListener('focus', onWindowFocus);
    }
  });

  return {
    notifications,
    unreadCount,
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    goToNotification,
    startListening,
    stopListening,
  };
}
