import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echoInstance = null;
let currentChannel = null;

/**
 * Initialize Laravel Echo (WebSocket) for real-time notifications.
 * Uses Reverb when VITE_REVERB_* env is set; otherwise no-op so app works without Reverb.
 * @param {number} userId - Current user id for private channel
 * @param {function} onNotification - Callback when a new notification is received (e.g. refetch list)
 */
export function initNotificationEcho(userId, onNotification) {
  if (!userId || typeof onNotification !== 'function') return;

  const key = import.meta.env.VITE_REVERB_APP_KEY;
  const wsHost = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
  const wsPort = import.meta.env.VITE_REVERB_PORT ?? 80;
  const wssPort = import.meta.env.VITE_REVERB_PORT ?? 443;
  const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
  const forceTLS = scheme === 'https';

  if (!key) {
    return;
  }

  disconnectNotificationEcho();

  const authUrl = `${window.location.origin}/api/broadcasting/auth`;

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key,
    wsHost,
    wsPort: Number(wsPort),
    wssPort: Number(wssPort),
    forceTLS,
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel) => ({
      authorize: (socketId, callback) => {
        const token = localStorage.getItem('token');
        if (!token) {
          callback(true, { message: 'No token' });
          return;
        }
        fetch(authUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            socket_id: socketId,
            channel_name: channel.name,
          }),
        })
          .then((r) => r.json())
          .then((data) => callback(false, data))
          .catch((err) => callback(true, err));
      },
    }),
  });

  const channelName = `App.Models.User.${userId}`;
  currentChannel = echoInstance.private(channelName);

  currentChannel.notification((payload) => {
    onNotification(payload);
  });
}

/**
 * Disconnect and clean up Echo subscription for notifications.
 */
export function disconnectNotificationEcho() {
  if (currentChannel) {
    try {
      echoInstance?.leave(currentChannel.name);
    } catch (_) {}
    currentChannel = null;
  }
  if (echoInstance) {
    try {
      echoInstance.disconnect();
    } catch (_) {}
    echoInstance = null;
  }
}
