import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import { initAuth, clearUser } from './auth';

initAuth();

axios.defaults.baseURL = '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            clearUser();
            if (router.currentRoute.value.meta.requiresAuth) {
                router.push({ name: 'login' });
            }
        }
        return Promise.reject(error);
    }
);

const app = createApp(App);
app.use(router);

app.directive('click-outside', {
    mounted(el, binding) {
        el._clickOutside = (e) => {
            if (!el.contains(e.target)) binding.value(e);
        };
        document.addEventListener('click', el._clickOutside);
    },
    unmounted(el) {
        document.removeEventListener('click', el._clickOutside);
    },
});

app.mount('#app');
