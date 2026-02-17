import { ref } from 'vue';

/** Reactive user state so navbar/layout update immediately after login without refresh. */
export const user = ref(null);

export function initAuth() {
  try {
    const stored = localStorage.getItem('user');
    if (stored) user.value = JSON.parse(stored);
  } catch {
    user.value = null;
  }
}

export function setUser(userData) {
  user.value = userData;
  if (userData) {
    localStorage.setItem('user', JSON.stringify(userData));
  } else {
    localStorage.removeItem('user');
  }
}

export function clearUser() {
  user.value = null;
  localStorage.removeItem('user');
}
