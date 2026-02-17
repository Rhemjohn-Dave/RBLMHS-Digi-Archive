<template>
  <div class="fixed inset-0 flex items-center justify-center overflow-y-auto bg-gradient-to-br from-slate-100 to-slate-200 px-4 py-8">
    <div class="w-full max-w-md">
      <!-- Logo + branding -->
      <div class="mb-6 flex flex-col items-center">
        <img
          v-if="logoSrc"
          :src="logoSrc"
          alt="Logo"
          class="h-32 w-auto object-contain"
          @error="logoSrc = ''"
        />
        <p class="mt-3 text-center text-lg font-semibold text-slate-800">RBLMHS Digi Archive</p>
        <p class="mt-0.5 text-center text-sm text-slate-500">Sign in to your account</p>
      </div>

      <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-semibold text-slate-800">Sign in</h1>
        <p class="mt-1 text-sm text-slate-500">Enter your credentials to continue.</p>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Username</label>
            <input
              v-model="form.username"
              type="text"
              required
              class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
              :class="errors.username ? 'border-red-400 bg-red-50/50' : 'border-slate-300'"
            />
            <p v-if="errors.username" class="mt-1 text-sm text-red-600">{{ errors.username }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
              :class="errors.password ? 'border-red-400 bg-red-50/50' : 'border-slate-300'"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-lg bg-slate-800 py-2.5 font-medium text-white hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:opacity-50"
          >
            {{ loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
          Don't have an account?
          <router-link to="/register" class="font-medium text-indigo-600 hover:underline">Register</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { setUser } from '../auth';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
// Logo: add a file at public/logo.png to display it (or set another path).
const logoSrc = ref('/logo.png');
const form = reactive({ username: '', password: '' });
const errors = reactive({});

async function submit() {
  errors.username = errors.password = '';
  loading.value = true;
  try {
    const { data } = await axios.post('/login', form);
    localStorage.setItem('token', data.token);
    setUser(data.user);
    router.push(route.query.redirect || '/');
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.errors;
    if (typeof msg === 'object') {
      if (msg.username) errors.username = msg.username[0];
      if (msg.password) errors.password = msg.password[0];
    } else if (msg) errors.username = msg;
    else errors.username = 'Invalid credentials.';
  } finally {
    loading.value = false;
  }
}
</script>
