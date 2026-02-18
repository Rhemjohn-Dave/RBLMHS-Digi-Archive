const STORAGE_KEY = 'rblmhs_recently_viewed';
const MAX_ITEMS = 5;

export function useRecentlyViewed() {
  function getList() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return [];
      const list = JSON.parse(raw);
      return Array.isArray(list) ? list.slice(0, MAX_ITEMS) : [];
    } catch {
      return [];
    }
  }

  function add({ id, title }) {
    if (!id || !title) return;
    let list = getList();
    list = list.filter((item) => item.id !== id);
    list.unshift({ id, title, viewedAt: Date.now() });
    list = list.slice(0, MAX_ITEMS);
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    } catch (_) {}
  }

  return { getList, add };
}
