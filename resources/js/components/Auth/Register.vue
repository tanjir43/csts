<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <h2 class="text-2xl font-bold text-center text-gray-900">Register</h2>
      <form @submit.prevent="handleRegister" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
          <input
            id="name"
            v-model="name"
            name="name"
            type="text"
            required
            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="Your Name"
          />
           <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name[0] }}</span>
        </div>
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
           <span v-if="errors.email" class="text-xs text-red-600">{{ errors.email[0] }}</span>
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
           <span v-if="errors.password" class="text-xs text-red-600">{{ errors.password[0] }}</span>
        </div>
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
          <input
            id="password_confirmation"
            v-model="password_confirmation"
            name="password_confirmation"
            type="password"
            required
            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="Confirm Password"
          />
        </div>
         <div v-if="errorMessage" class="text-sm text-red-600">
            {{ errorMessage }}
        </div>
        <div>
          <button
            type="submit"
            :disabled="loading"
            class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
             <span v-if="loading">Registering...</span>
            <span v-else>Register</span>
          </button>
        </div>
      </form>
       <p class="mt-4 text-sm text-center text-gray-600">
            Already have an account?
            <router-link :to="{ name: 'login' }" class="font-medium text-indigo-600 hover:text-indigo-500">
                Login here
            </router-link>
        </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuth } from '../../store/auth';

const router = useRouter();
const { setUserData } = useAuth();
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const errorMessage = ref('');
const errors = ref({});
const loading = ref(false);

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};
  try {
    await axios.get('/sanctum/csrf-cookie');

    const response = await axios.post('/api/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    });

    if (response.data && response.data.user) {
        setUserData(response.data.user);
        router.push({ name: 'tickets.index' });
    } else {
        console.warn('Registration successful, but user data not found in response. Redirecting to login.');
        router.push({ name: 'login' });
    }

  } catch (error) {
    console.error('Registration failed:', error);
     if (error.response && error.response.status === 422) {
        errors.value = error.response.data.errors;
        errorMessage.value = 'Please fix the errors above.';
    } else if (error.response && error.response.data && error.response.data.message) {
         errorMessage.value = error.response.data.message;
    }
     else {
      errorMessage.value = 'An unexpected error occurred during registration. Please try again.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
