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
        <p class="mt-1 text-sm text-slate-500">Create an account</p>
      </div>

      <div class="card overflow-hidden">
        <div class="border-l-4 border-slate-800 bg-slate-50 px-6 py-4">
          <h2 class="text-lg font-semibold text-slate-800">Register</h2>
          <p class="mt-0.5 text-sm text-slate-500">Fill in your details to get started.</p>
        </div>
        <form @submit.prevent="submit" class="space-y-4 p-6">
          <div>
            <label class="label-base" for="reg-role">I am a</label>
            <select id="reg-role" v-model="form.role" class="input-base">
              <option value="student">Student</option>
              <option value="faculty">Faculty</option>
            </select>
          </div>
          <div v-if="form.role === 'student'">
            <label class="label-base" for="reg-lrn">LRN *</label>
            <input id="reg-lrn" v-model="form.lrn" type="text" class="input-base" :class="{ 'input-error': errors.lrn }" />
            <p v-if="errors.lrn" class="mt-1 text-sm text-red-600">{{ errors.lrn }}</p>
          </div>
          <div v-else>
            <label class="label-base" for="reg-faculty_number">Faculty Number *</label>
            <input id="reg-faculty_number" v-model="form.faculty_number" type="text" class="input-base" :class="{ 'input-error': errors.faculty_number }" />
            <p v-if="errors.faculty_number" class="mt-1 text-sm text-red-600">{{ errors.faculty_number }}</p>
          </div>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="label-base" for="reg-family_name">Family Name *</label>
              <input id="reg-family_name" v-model="form.family_name" type="text" required class="input-base" :class="{ 'input-error': errors.family_name }" />
              <p v-if="errors.family_name" class="mt-1 text-sm text-red-600">{{ errors.family_name }}</p>
            </div>
            <div>
              <label class="label-base" for="reg-given_name">Given Name *</label>
              <input id="reg-given_name" v-model="form.given_name" type="text" required class="input-base" :class="{ 'input-error': errors.given_name }" />
              <p v-if="errors.given_name" class="mt-1 text-sm text-red-600">{{ errors.given_name }}</p>
            </div>
          </div>
          <div>
            <label class="label-base" for="reg-middle_name">Middle Name</label>
            <input id="reg-middle_name" v-model="form.middle_name" type="text" class="input-base" />
          </div>
          <div>
            <label class="label-base" for="reg-username">Username *</label>
            <input id="reg-username" v-model="form.username" type="text" required class="input-base" :class="{ 'input-error': errors.username }" />
            <p v-if="errors.username" class="mt-1 text-sm text-red-600">{{ errors.username }}</p>
          </div>
          <div>
            <label class="label-base" for="reg-password">Password *</label>
            <input id="reg-password" v-model="form.password" type="password" required class="input-base" :class="{ 'input-error': errors.password }" />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            <div v-if="form.password" class="mt-1.5 flex items-center gap-2">
              <span class="text-xs font-medium" :class="passwordStrengthClass">{{ passwordStrengthLabel }}</span>
              <span class="text-xs text-slate-400">·</span>
              <span class="text-xs text-slate-500">At least 8 characters recommended</span>
            </div>
          </div>
          <div>
            <label class="label-base" for="reg-password_confirmation">Confirm Password *</label>
            <input id="reg-password_confirmation" v-model="form.password_confirmation" type="password" required class="input-base" />
          </div>
          <button type="submit" :disabled="loading" class="btn-primary w-full">
            <span v-if="loading" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" aria-hidden="true" />
            {{ loading ? 'Registering…' : 'Register' }}
          </button>
        </form>
        <p class="border-t border-slate-100 px-6 py-4 text-center text-sm text-slate-600">
          Already have an account?
          <router-link to="/login" class="font-medium text-slate-800 underline hover:no-underline">Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { setUser } from '../auth';
import { useToast } from '../composables/useToast';

const router = useRouter();
const toast = useToast();
const loading = ref(false);
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

function getPasswordStrength(pwd) {
  if (!pwd) return { label: '', class: '' };
  const len = pwd.length;
  const hasLower = /[a-z]/.test(pwd);
  const hasUpper = /[A-Z]/.test(pwd);
  const hasNumber = /\d/.test(pwd);
  const hasSpecial = /[^A-Za-z0-9]/.test(pwd);
  let score = 0;
  if (len >= 8) score += 1;
  if (len >= 12) score += 1;
  if (hasLower && hasUpper) score += 1;
  if (hasNumber) score += 1;
  if (hasSpecial) score += 1;
  if (score <= 1) return { label: 'Weak', class: 'text-red-600' };
  if (score <= 3) return { label: 'Fair', class: 'text-amber-600' };
  return { label: 'Strong', class: 'text-emerald-600' };
}

const passwordStrengthLabel = computed(() => getPasswordStrength(form.password).label);
const passwordStrengthClass = computed(() => getPasswordStrength(form.password).class);

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
    toast.success('Account created. Welcome!');
    router.push('/');
  } catch (e) {
    const d = e.response?.data?.errors || {};
    Object.entries(d).forEach(([k, v]) => (errors[k] = Array.isArray(v) ? v[0] : v));
    if (Object.keys(errors).length === 0 && e.response?.data?.message) errors.username = e.response.data.message;
    const errMsg = Object.values(errors).find(Boolean);
    if (errMsg) toast.error(errMsg);
  } finally {
    loading.value = false;
  }
}
</script>
