import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  { path: '/', name: 'home', component: () => import('../views/ResearchList.vue'), meta: { requiresAuth: true } },
  { path: '/login', name: 'login', component: () => import('../views/Login.vue'), meta: { guest: true } },
  { path: '/register', name: 'register', component: () => import('../views/Register.vue'), meta: { guest: true } },
  { path: '/research', name: 'research', component: () => import('../views/ResearchList.vue'), meta: { requiresAuth: true } },
  { path: '/research/:id', name: 'research-show', component: () => import('../views/ResearchShow.vue'), meta: { requiresAuth: true } },
  { path: '/upload', name: 'upload', component: () => import('../views/UploadResearch.vue'), meta: { requiresAuth: true, faculty: true } },
  { path: '/my-submissions', name: 'my-submissions', component: () => import('../views/MySubmissions.vue'), meta: { requiresAuth: true, faculty: true } },
  { path: '/admin', name: 'admin', component: () => import('../views/admin/Dashboard.vue'), meta: { requiresAuth: true, admin: true } },
  { path: '/admin/users', name: 'admin-users', component: () => import('../views/admin/UserManagement.vue'), meta: { requiresAuth: true, admin: true } },
  { path: '/admin/research', name: 'admin-research', component: () => import('../views/admin/ResearchManagement.vue'), meta: { requiresAuth: true, admin: true } },
  { path: '/admin/download-requests', name: 'admin-download-requests', component: () => import('../views/admin/DownloadRequests.vue'), meta: { requiresAuth: true, admin: true } },
  { path: '/admin/download-logs', name: 'admin-download-logs', component: () => import('../views/admin/DownloadLogs.vue'), meta: { requiresAuth: true, admin: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  const user = (() => {
    try {
      return JSON.parse(localStorage.getItem('user') || 'null');
    } catch {
      return null;
    }
  })();

  if (to.meta.requiresAuth && !token) {
    next({ name: 'login', query: { redirect: to.fullPath } });
    return;
  }
  if (to.meta.guest && token) {
    next({ name: 'home' });
    return;
  }
  if (to.meta.admin && user?.role !== 'admin') {
    next({ name: 'home' });
    return;
  }
  if (to.meta.faculty && user?.role !== 'faculty') {
    next({ name: 'home' });
    return;
  }
  next();
});

export default router;
