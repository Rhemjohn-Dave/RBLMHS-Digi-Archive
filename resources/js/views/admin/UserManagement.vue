<template>
  <div>
    <h1 class="mb-6 text-2xl font-semibold text-slate-800">User Management</h1>
    <div class="mb-4 flex flex-wrap gap-2">
      <input v-model="search" type="search" placeholder="Search..." class="rounded-lg border border-slate-300 px-3 py-2 w-48" />
      <select v-model="roleFilter" class="rounded-lg border border-slate-300 px-3 py-2">
        <option value="">All roles</option>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
        <option value="admin">Admin</option>
      </select>
      <select v-model="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2">
        <option value="">All status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
      <button type="button" @click="fetch(1)" class="rounded-lg bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">Filter</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Username</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Role</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Status</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-slate-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr v-for="u in list.data" :key="u.id" class="hover:bg-slate-50">
            <td class="px-4 py-2 text-sm">{{ u.username }}</td>
            <td class="px-4 py-2 text-sm">{{ u.given_name }} {{ u.family_name }}</td>
            <td class="px-4 py-2 text-sm">{{ u.role }}</td>
            <td class="px-4 py-2">
              <span :class="u.status === 'approved' ? 'bg-green-100 text-green-800' : u.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'" class="rounded px-2 py-0.5 text-xs font-medium">{{ u.status }}</span>
            </td>
            <td class="px-4 py-2">
              <template v-if="u.role !== 'admin' && u.status === 'pending'">
                <button type="button" @click="approve(u.id)" class="mr-2 text-sm text-green-600 hover:underline">Approve</button>
                <button type="button" @click="reject(u.id)" class="text-sm text-red-600 hover:underline">Reject</button>
              </template>
              <button v-if="u.role !== 'admin'" type="button" @click="del(u.id)" class="ml-2 text-sm text-red-600 hover:underline">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="list.data?.length && list.last_page > 1" class="mt-4 flex justify-center gap-2">
      <button :disabled="list.current_page === 1" @click="fetch(list.current_page - 1)" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50">Prev</button>
      <span class="py-1 text-sm">Page {{ list.current_page }} of {{ list.last_page }}</span>
      <button :disabled="list.current_page === list.last_page" @click="fetch(list.current_page + 1)" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50">Next</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const search = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const loading = ref(true);
const list = ref({ data: [], current_page: 1, last_page: 1 });

function fetch(page = 1) {
  loading.value = true;
  const params = { page, per_page: 15 };
  if (search.value) params.search = search.value;
  if (roleFilter.value) params.role = roleFilter.value;
  if (statusFilter.value) params.status = statusFilter.value;
  axios.get('/admin/users', { params }).then(({ data }) => {
    list.value = data;
    loading.value = false;
  }).catch(() => { loading.value = false; });
}

function approve(id) {
  axios.post(`/admin/users/${id}/approve`).then(() => fetch(list.value.current_page));
}

function reject(id) {
  axios.post(`/admin/users/${id}/reject`).then(() => fetch(list.value.current_page));
}

function del(id) {
  if (!confirm('Delete this user?')) return;
  axios.delete(`/admin/users/${id}`).then(() => fetch(list.value.current_page));
}

onMounted(() => fetch());
</script>
