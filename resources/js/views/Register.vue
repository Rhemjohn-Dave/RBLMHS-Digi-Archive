<template>
  <div class="fixed inset-0 flex items-center justify-center overflow-y-auto bg-gradient-to-br from-slate-100 to-slate-200 px-4 py-8">
    <div class="w-full max-w-md">
      <!-- Logo + branding -->
      <div class="mb-6 flex flex-col items-center">
        <img
          v-if="logoSrc"
          :src="logoSrc"
          alt="Logo"
          class="h-16 w-auto object-contain"
          @error="logoSrc = ''"
        />
        <p class="mt-3 text-center text-lg font-semibold text-slate-800">RBLMHS Digi Archive</p>
        <p class="mt-0.5 text-center text-sm text-slate-500">Create an account</p>
      </div>

      <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-semibold text-slate-800">Register</h1>
        <p class="mt-1 text-sm text-slate-500">Fill in your details to get started.</p>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">I am a</label>
            <select
              v-model="form.role"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            >
              <option value="student">Student</option>
              <option value="faculty">Faculty</option>
            </select>
          </div>

          <template v-if="form.role === 'student'">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-600">LRN *</label>
              <input
                v-model="form.lrn"
                type="text"
                class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                :class="errors.lrn ? 'border-red-400 bg-red-50/50' : 'border-slate-300'"
              />
              <p v-if="errors.lrn" class="mt-1 text-sm text-red-600">{{ errors.lrn }}</p>
            </div>
          </template>
          <template v-else>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-600">Faculty Number *</label>
              <input
                v-model="form.faculty_number"
                type="text"
                class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                :class="errors.faculty_number ? 'border-red-400 bg-red-50/50' : 'border-slate-300'"
              />
              <p v-if="errors.faculty_number" class="mt-1 text-sm text-red-600">{{ errors.faculty_number }}</p>
            </div>
          </template>

          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Family Name *</label>
            <input
              v-model="form.family_name"
              type="text"
              required
              class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
              :class="errors.family_name ? 'border-red-400 bg-red-50/50' : 'border-slate-300'"
            />
            <p v-if="errors.family_name" class="mt-1 text-sm text-red-600">{{ errors.family_name }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Given Name *</label>
            <input
              v-model="form.given_name"
              type="text"
              required
              class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
              :class="errors.given_name ? 'border-red-400 bg-red-50/50' : 'border-slate-300'"
            />
            <p v-if="errors.given_name" class="mt-1 text-sm text-red-600">{{ errors.given_name }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Middle Name</label>
            <input
              v-model="form.middle_name"
              type="text"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Username *</label>
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
            <label class="mb-1 block text-sm font-medium text-slate-600">Password *</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
              :class="errors.password ? 'border-red-400 bg-red-50/50' : 'border-slate-300'"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Confirm Password *</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
            />
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-lg bg-slate-800 py-2.5 font-medium text-white hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:opacity-50"
          >
            {{ loading ? 'Registering...' : 'Register' }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
          Already have an account?
          <router-link to="/login" class="font-medium text-indigo-600 hover:underline">Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { setUser } from '../auth';

const router = useRouter();
const loading = ref(false);
// Logo: add a file at public/logo.png to display it (or set another path).
const logoSrc = ref('/logo.png');
const form = reactive({
  role: 'student',
  lrn: '',
  faculty_number: '',
  family_name: '',
  given_name: '',
  middle_name: '',
  username: '',
  password: '',
  password_confirmation: '',
});
const errors = reactive({});

watch(() => form.role, () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
});

async function submit() {
  Object.keys(errors).forEach((k) => delete errors[k]);
  loading.value = true;
  try {
    const payload = {
      role: form.role,
      family_name: form.family_name,
      given_name: form.given_name,
      middle_name: form.middle_name || null,
      username: form.username,
      password: form.password,
      password_confirmation: form.password_confirmation,
    };
    if (form.role === 'student') payload.lrn = form.lrn;
    else payload.faculty_number = form.faculty_number;

    const { data } = await axios.post('/register', payload);
    localStorage.setItem('token', data.token);
    setUser(data.user);
    router.push('/');
  } catch (e) {
    const d = e.response?.data?.errors || {};
    Object.entries(d).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v));
    if (Object.keys(errors).length === 0 && e.response?.data?.message) errors.username = e.response.data.message;
  } finally {
    loading.value = false;
  }
}
</script>
