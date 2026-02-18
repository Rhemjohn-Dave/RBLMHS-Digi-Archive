<template>
  <div>
    <Breadcrumbs :items="[{ label: 'Admin', to: '/admin' }, { label: 'Users', to: '' }]" class="mb-2" />
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
    <div v-if="loading" class="flex flex-col gap-3 py-8">
      <div v-for="i in 6" :key="i" class="flex gap-4 rounded-lg border border-slate-200 bg-white p-4">
        <Skeleton class="h-4 w-24" />
        <Skeleton class="h-4 flex-1" />
        <Skeleton class="h-4 w-16" />
      </div>
    </div>
    <EmptyState
      v-else-if="!list.data?.length"
      title="No users found"
      description="Users will appear here once they register."
    />
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow max-h-[70vh] overflow-y-auto">
      <p class="mb-2 text-xs text-slate-500 sm:hidden">Scroll horizontally to see all columns.</p>
      <table class="min-w-[36rem] w-full divide-y divide-slate-200">
        <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Username</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Name</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Role</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Status</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-slate-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr v-for="u in list.data" :key="u.id" class="transition-colors hover:bg-slate-50/80">
            <td class="px-4 py-3 text-sm">{{ u.username }}</td>
            <td class="px-4 py-3 text-sm">{{ u.given_name }} {{ u.family_name }}</td>
            <td class="px-4 py-3 text-sm">{{ u.role }}</td>
            <td class="px-4 py-3">
              <span :class="u.status === 'approved' ? 'bg-green-100 text-green-800' : u.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'" class="rounded px-2 py-0.5 text-xs font-medium">{{ u.status }}</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <template v-if="u.role !== 'admin' && u.status === 'pending'">
                  <button type="button" @click="approve(u.id)" class="rounded p-1.5 text-green-600 hover:bg-green-50 transition-colors" title="Approve" aria-label="Approve user">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  </button>
                  <button type="button" @click="reject(u.id)" class="rounded p-1.5 text-red-600 hover:bg-red-50 transition-colors" title="Reject" aria-label="Reject user">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                  </button>
                </template>
                <button v-if="u.role !== 'admin'" type="button" @click="del(u.id)" class="rounded p-1.5 text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete" aria-label="Delete user">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination
      v-if="list.data?.length"
      :current-page="list.current_page"
      :last-page="list.last_page"
      @page="(p) => fetch(p)"
      class="mt-4"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../../composables/useToast';
import Breadcrumbs from '../../components/Breadcrumbs.vue';
import EmptyState from '../../components/EmptyState.vue';
import Pagination from '../../components/Pagination.vue';
import Skeleton from '../../components/Skeleton.vue';

const toast = useToast();
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
  axios.post(`/admin/users/${id}/approve`).then(() => {
    toast.success('User approved.');
    fetch(list.value.current_page);
  }).catch(() => toast.error('Failed to approve user.'));
}

function reject(id) {
  axios.post(`/admin/users/${id}/reject`).then(() => {
    toast.success('User rejected.');
    fetch(list.value.current_page);
  }).catch(() => toast.error('Failed to reject user.'));
}

function del(id) {
  if (!confirm('Delete this user?')) return;
  axios.delete(`/admin/users/${id}`).then(() => {
    toast.success('User deleted.');
    fetch(list.value.current_page);
  }).catch(() => toast.error('Failed to delete user.'));
}

onMounted(() => fetch());
</script>
