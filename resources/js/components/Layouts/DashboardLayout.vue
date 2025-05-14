<template>
  <div class="flex flex-col min-h-screen bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
      <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex items-center flex-shrink-0">
              <!-- Logo or App Name -->
              <router-link :to="{ name: 'tickets.index' }" class="text-xl font-bold text-indigo-600">
                Support System
              </router-link>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link
                :to="{ name: 'tickets.index' }"
                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-indigo-500"
                active-class="border-indigo-500 text-gray-900"
                inactive-class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700"
                >Tickets</router-link
              >
               <router-link
                :to="{ name: 'tickets.create' }"
                 class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700"
                 active-class="border-indigo-500 text-gray-900"
                 inactive-class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700"
                >Create Ticket</router-link
              >
            </div>
          </div>
          <div class="hidden sm:ml-6 sm:flex sm:items-center">
             <span class="mr-4 text-sm text-gray-600">Welcome, {{ userName }}</span>
            <button
              @click="handleLogout"
              type="button"
              class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
              :disabled="loggingOut"
            >
              <span v-if="loggingOut">Logging out...</span>
              <span v-else>Logout</span>
            </button>
          </div>
          <div class="flex items-center -mr-2 sm:hidden">
             <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 text-gray-400 bg-white rounded-md hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg v-if="!mobileMenuOpen" class="block w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg v-else class="block w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
             </button>
          </div>
        </div>
      </div>

        <div v-show="mobileMenuOpen" class="sm:hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
            <router-link :to="{ name: 'tickets.index' }" @click="mobileMenuOpen = false" class="block py-2 pl-3 pr-4 text-base font-medium text-indigo-700 border-l-4 border-indigo-500 bg-indigo-50" active-class="bg-indigo-50 border-indigo-500 text-indigo-700" inactive-class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">Tickets</router-link>
            <router-link :to="{ name: 'tickets.create' }" @click="mobileMenuOpen = false" class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 border-l-4 border-transparent hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800" active-class="bg-indigo-50 border-indigo-500 text-indigo-700" inactive-class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">Create Ticket</router-link>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">{{ userName }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ userEmail }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <button @click="handleLogout" class="block w-full px-4 py-2 text-base font-medium text-left text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
      <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="px-4 py-6 bg-white shadow sm:rounded-lg sm:px-0">
          <router-view></router-view>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white">
        <div class="px-4 py-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
            <p class="text-sm text-gray-500">&copy; {{ new Date().getFullYear() }} Support System. All rights reserved.</p>
        </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../store/auth';

const router = useRouter();
const { user, logout } = useAuth();
const loggingOut = ref(false);
const mobileMenuOpen = ref(false);

const userName = computed(() => user.value?.name || 'User');
const userEmail = computed(() => user.value?.email || '');

const handleLogout = async () => {
  loggingOut.value = true;
  await logout();
  loggingOut.value = false;
};

</script>

<style scoped>
</style>
