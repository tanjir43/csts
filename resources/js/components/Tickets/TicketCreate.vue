<template>
  <div class="p-4 mx-auto max-w-2xl">
    <h1 class="mb-6 text-3xl font-bold text-gray-800">Create New Ticket</h1>
    <form @submit.prevent="submitTicket" class="p-6 space-y-6 bg-white rounded-lg shadow-md">
      <div>
        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
        <input
          type="text"
          id="subject"
          v-model="ticket.subject"
          required
          class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        />
        <span v-if="errors.subject" class="text-xs text-red-600">{{ errors.subject[0] }}</span>
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea
          id="description"
          v-model="ticket.description"
          required
          rows="4"
          class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        ></textarea>
         <span v-if="errors.description" class="text-xs text-red-600">{{ errors.description[0] }}</span>
      </div>

      <div>
        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
        <select
          id="category"
          v-model="ticket.category"
          required
          class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
          <option disabled value="">Please select a category</option>
          <option value="Technical">Technical</option>
          <option value="Billing">Billing</option>
          <option value="General">General</option>
        </select>
        <span v-if="errors.category" class="text-xs text-red-600">{{ errors.category[0] }}</span>
      </div>

      <div>
        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
        <select
          id="priority"
          v-model="ticket.priority"
          required
          class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
          <option disabled value="">Please select a priority</option>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>
         <span v-if="errors.priority" class="text-xs text-red-600">{{ errors.priority[0] }}</span>
      </div>

      <div>
        <label for="attachment" class="block text-sm font-medium text-gray-700">Attachment (Optional)</label>
        <input
          type="file"
          id="attachment"
          @change="handleFileUpload"
          class="block w-full mt-1 text-sm text-gray-500 border border-gray-300 rounded-md cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
        />
        <span v-if="errors.attachment" class="text-xs text-red-600">{{ errors.attachment[0] }}</span>
      </div>
       <div v-if="successMessage" class="text-sm text-green-600">
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="text-sm text-red-600">
            {{ errorMessage }}
        </div>

      <div class="flex justify-end space-x-3">
        <router-link
            :to="{ name: 'tickets.index' }"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >Cancel</router-link>
        <button
          type="submit"
          :disabled="loading"
          class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          <span v-if="loading">Submitting...</span>
          <span v-else>Submit Ticket</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const ticket = ref({
  subject: '',
  description: '',
  category: '',
  priority: '',
  attachment: null,
});
const errors = ref({});
const errorMessage = ref('');
const successMessage = ref('');
const loading = ref(false);

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    ticket.value.attachment = file;
  }
};

const submitTicket = async () => {
  loading.value = true;
  errors.value = {};
  errorMessage.value = '';
  successMessage.value = '';

  errors.value = {};
  let isValid = true;
  if (!ticket.value.subject.trim()) {
    errors.value.subject = ['The subject field is required.'];
    isValid = false;
  }
  if (!ticket.value.description.trim()) {
    errors.value.description = ['The description field is required.'];
    isValid = false;
  }
  if (!ticket.value.category) {
    errors.value.category = ['The category field is required.'];
    isValid = false;
  }
  if (!ticket.value.priority) {
    errors.value.priority = ['The priority field is required.'];
    isValid = false;
  }

  if (!isValid) {
    errorMessage.value = 'Please fill in all required fields.';
    loading.value = false;
    return;
  }

  const formData = new FormData();
  formData.append('subject', ticket.value.subject);
  formData.append('description', ticket.value.description);
  formData.append('category', ticket.value.category.toLowerCase());
  formData.append('priority', ticket.value.priority.toLowerCase());
  formData.append('status', 'open');
  if (ticket.value.attachment) {
    formData.append('attachment', ticket.value.attachment);
  }

  try {
    await axios.post('/api/tickets', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    successMessage.value = 'Ticket created successfully!';
    ticket.value = { subject: '', description: '', category: '', priority: '', attachment: null };

    setTimeout(() => {
        router.push({ name: 'tickets.index' });
    }, 1500);

  } catch (err) {
    console.error('Failed to create ticket:', err);
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors;
      errorMessage.value = 'Please correct the errors above.';
    } else {
      errorMessage.value = err.response?.data?.message || err.message || 'Could not create ticket.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
