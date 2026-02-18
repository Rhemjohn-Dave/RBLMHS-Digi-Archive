<template>
  <div class="fixed inset-0 flex items-center justify-center overflow-y-auto bg-gradient-to-br from-slate-100 via-white to-slate-100 px-4 py-10">
    <div class="w-full max-w-md">
      <div class="mb-8 flex flex-col items-center text-center">
        <img
          v-if="logoSrc"
          :src="logoSrc"
          alt="RBLMHS Digi Archive logo"
          class="h-24 w-auto object-contain"
          @error="logoSrc = ''"
        />
        <h1 class="mt-4 text-xl font-bold text-slate-900">RBLMHS Digi Archive</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to your account</p>
      </div>

      <div class="card overflow-hidden">
        <div class="border-l-4 border-slate-800 bg-slate-50 px-6 py-4">
          <h2 class="text-lg font-semibold text-slate-800">Sign in</h2>
          <p class="mt-0.5 text-sm text-slate-500">Enter your credentials to continue.</p>
        </div>
        <form @submit.prevent="submit" class="space-y-4 p-6">
          <div>
            <label class="label-base" for="login-username">Username</label>
            <input
              id="login-username"
              v-model="form.username"
              type="text"
              required
              autocomplete="username"
              class="input-base"
              :class="{ 'input-error': errors.username }"
            />
            <p v-if="errors.username" class="mt-1 text-sm text-red-600">{{ errors.username }}</p>
          </div>
          <div>
            <label class="label-base" for="login-password">Password</label>
            <input
              id="login-password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              class="input-base"
              :class="{ 'input-error': errors.password }"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full"
          >
            <span v-if="loading" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" aria-hidden="true" />
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>
        <p class="border-t border-slate-100 px-6 py-4 text-center text-sm text-slate-600">
          Don't have an account?
          <router-link to="/register" class="font-medium text-slate-800 underline hover:no-underline">Register</router-link>
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
import { useToast } from '../composables/useToast';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const loading = ref(false);
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
    toast.error(errors.username || 'Invalid credentials.');
  } finally {
    loading.value = false;
  }
}
</script>
