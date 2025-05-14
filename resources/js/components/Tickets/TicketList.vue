<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-gray-800">{{ pageTitle }}</h1>
      <router-link
        :to="{ name: 'tickets.create' }"
        class="px-4 py-2 font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        Create New Ticket
      </router-link>
    </div>

    <div v-if="loading" class="text-center text-gray-500">Loading tickets...</div>
    <div v-if="error" class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
      Error loading tickets: {{ error }}
      <div v-if="loginRequired" class="mt-2">
        <button
          @click="redirectToLogin"
          class="px-4 py-2 font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
        >
          Login Again
        </button>
      </div>
    </div>

    <div v-if="!loading && !error && tickets.length === 0" class="p-4 text-center text-gray-500 bg-white rounded-lg shadow">
      You haven't created any tickets yet.
    </div>

    <div v-if="!loading && !error && tickets.length > 0" class="space-y-4">
      <div
        v-for="ticket in tickets"
        :key="ticket.id"
        class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg"
      >
        <router-link :to="{ name: 'tickets.show', params: { id: ticket.id } }" class="block">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-indigo-700">{{ ticket.subject }}</h2>
            <span
              :class="getStatusClass(ticket.status)"
              class="px-3 py-1 text-xs font-semibold rounded-full"
            >
              {{ ticket.status }}
            </span>
          </div>
          <p class="mt-2 text-sm text-gray-600 truncate">{{ ticket.description }}</p>
          <div class="flex items-center justify-between mt-4 text-xs text-gray-500">
            <span>Category: {{ ticket.category }}</span>
            <span>Priority: {{ ticket.priority }}</span>
            <span>Last Updated: {{ formatDate(ticket.updated_at) }}</span>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.meta && pagination.meta.last_page > 1" class="flex items-center justify-between mt-6">
      <span class="text-sm text-gray-700">
        Showing {{ pagination.meta.from }} to {{ pagination.meta.to }} of {{ pagination.meta.total }} results
      </span>
      <div class="flex items-center space-x-1">
        <button
          v-for="link in pagination.links"
          :key="link.label"
          @click.prevent="changePage(link.url)"
          :disabled="!link.url || link.active"
          :class="[
            'px-3 py-1 text-sm rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1',
            link.active ? 'bg-indigo-600 text-white cursor-default' : 'bg-white text-gray-700 hover:bg-gray-100',
            !link.url ? 'text-gray-400 cursor-not-allowed bg-gray-50' : ''
          ]"
          v-html="link.label"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuth } from '../../store/auth';
import { useRouter } from 'vue-router';

const router = useRouter();
const { user, fetchUser, logout } = useAuth();

const tickets = ref([]);
const loading = ref(true);
const error = ref(null);
const pagination = ref(null);
const loginRequired = ref(false);
const fetchAttempted = ref(false);

const pageTitle = computed(() => {
    const isAdmin = user.value?.roles?.some(role => role.name === 'Admin');
    return isAdmin ? 'All Tickets' : 'My Tickets';
});

const redirectToLogin = async () => {
  await logout();
  router.push({
    name: 'login',
    query: { redirect: router.currentRoute.value.fullPath }
  });
};

const fetchTickets = async (url = '/api/tickets') => {
  if (fetchAttempted.value && loginRequired.value) {
    console.log('Not attempting to fetch tickets again until user re-authenticates');
    return;
  }

  loading.value = true;
  error.value = null;
  loginRequired.value = false;

  try {
    console.log('Starting to fetch tickets');

    const accessToken = localStorage.getItem('access_token');
    if (!accessToken) {
      console.log('No access token found, authentication required');
      error.value = 'You need to log in to view tickets.';
      loginRequired.value = true;
      loading.value = false;
      fetchAttempted.value = true;
      return;
    }

    if (!user.value) {
      console.log('No user found, attempting to fetch user data');
      const userData = await fetchUser();
      if (!userData) {
        console.log('Failed to get user data, authentication required');
        error.value = 'You need to log in to view tickets.';
        loginRequired.value = true;
        loading.value = false;
        fetchAttempted.value = true;
        return;
      }
    }

    console.log('Making API call to fetch tickets');
    const response = await axios.get(url, {
      headers: {
        'Authorization': `${localStorage.getItem('token_type') || 'Bearer'} ${accessToken}`
      }
    });

    fetchAttempted.value = false;

    if (response.data && response.data.tickets && Array.isArray(response.data.tickets.data)) {
      tickets.value = response.data.tickets.data;
      pagination.value = response.data.tickets.links && response.data.tickets.meta ? {
        links: response.data.tickets.links,
        meta: response.data.tickets.meta,
      } : null;
      console.log('Successfully loaded tickets:', tickets.value.length);
    } else {
      console.error('Failed to fetch tickets: Unexpected API response format', response);
      error.value = 'Received unexpected data format from the server.';
      tickets.value = [];
      pagination.value = null;
    }
  } catch (err) {
    console.error('Failed to fetch tickets:', err);
    fetchAttempted.value = true;

    if (err.response && err.response.status === 401) {
      error.value = 'Authentication failed. Please log in again.';
      loginRequired.value = true;
    } else {
      error.value = err.response?.data?.message || err.message || 'Could not fetch tickets.';
    }
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const getStatusClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'open':
      return 'bg-blue-100 text-blue-800';
    case 'in progress':
      return 'bg-yellow-100 text-yellow-800';
    case 'resolved':
      return 'bg-green-100 text-green-800';
    case 'closed':
      return 'bg-gray-100 text-gray-800';
    default:
      return 'bg-gray-200 text-gray-700';
  }
};

const changePage = (url) => {
  if (url) {
    fetchTickets(url);
  }
};

onMounted(async () => {
  console.log('TicketList component mounted');
  try {
    if (!user.value) {
      console.log('No user in memory, fetching from API');
      await fetchUser();
    }

    fetchTickets();
  } catch (err) {
    console.error('Error during component mount:', err);
    error.value = 'Failed to initialize. Please try refreshing the page.';
    loading.value = false;
  }
});
</script>
