<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <h2 class="text-2xl font-bold text-center text-gray-900">Login</h2>
      <form @submit.prevent="login" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
          <input
            id="email"
            v-model="email"
            name="email"
            type="email"
            required
            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="you@example.com"
          />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            id="password"
            v-model="password"
            name="password"
            type="password"
            required
            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="Password"
          />
        </div>
        <div>
          <label for="rememberMe" class="flex items-center">
            <input
              id="rememberMe"
              v-model="rememberMe"
              type="checkbox"
              class="form-checkbox h-4 w-4 text-indigo-600"
            />
            <span class="ml-2 text-sm text-gray-700">Remember me</span>
          </label>
        </div>
        <div v-if="generalError" class="text-sm text-red-600">
          {{ generalError }}
        </div>
        <div>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="isSubmitting">Logging in...</span>
            <span v-else>Login</span>
          </button>
        </div>
      </form>
      <p class="mt-4 text-sm text-center text-gray-600">
        Don't have an account?
        <router-link :to="{ name: 'register' }" class="font-medium text-indigo-600 hover:text-indigo-500">
          Register here
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAuth } from '../../store/auth';
import { useRouter, useRoute } from 'vue-router';

const auth = useAuth();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const errors = ref({});
const isSubmitting = ref(false);
const generalError = ref('');

const redirectPath = computed(() => route.query.redirect || '/');

watch(auth.isAuthenticated, (newVal) => {
    if (newVal) {
        console.log('isAuthenticated changed to true, redirecting to:', redirectPath.value);
        router.push(redirectPath.value);
    }
});

const login = async () => {
    if (isSubmitting.value) return;

    isSubmitting.value = true;
    errors.value = {};
    generalError.value = '';

    try {
        console.log('Attempting login...');
        await auth.login({
            email: email.value,
            password: password.value,
            remember: rememberMe.value
        });

    } catch (error) {
        console.error('Login error:', error);

        if (error.response) {
            console.log('Server error status:', error.response.status);

            if (error.response.status === 422) {
                errors.value = error.response.data.errors || {};
            } else if (error.response.status === 401) {
                generalError.value = 'Invalid credentials. Please check your email and password.';
            } else {
                generalError.value = error.response.data.message || 'An unexpected error occurred.';
            }
        } else if (error.request) {
            generalError.value = 'No response from server. Please check your internet connection.';
        } else {
            generalError.value = 'An error occurred while sending your request.';
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>
