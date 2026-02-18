<template>
  <footer class="mt-auto border-t border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-sm font-semibold text-slate-800">RBLMHS Digi Archive</p>
          <p class="mt-0.5 text-xs text-slate-500">
            Digital Research Portal · Rafael B. Lacson Memorial High School
          </p>
        </div>
        <nav class="flex flex-wrap items-center gap-4 text-sm text-slate-600" aria-label="Footer">
          <router-link v-if="user" to="/research" class="hover:text-slate-900 transition-colors">
            Research
          </router-link>
          <template v-if="user?.role === 'faculty'">
            <router-link to="/my-submissions" class="hover:text-slate-900 transition-colors">
              My Submissions
            </router-link>
            <router-link to="/upload" class="hover:text-slate-900 transition-colors">
              Upload
            </router-link>
          </template>
          <router-link v-if="!user" to="/login" class="hover:text-slate-900 transition-colors">
            Sign in
          </router-link>
        </nav>
      </div>
      <p class="mt-6 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
        © {{ new Date().getFullYear() }} RBLMHS Digi Archive. All rights reserved.
      </p>
    </div>
  </footer>
</template>

<script setup>
import { user } from '../auth';
</script>
