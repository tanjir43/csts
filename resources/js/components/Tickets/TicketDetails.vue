<template>
    <div class="p-4 mx-auto max-w-3xl">
      <div v-if="loading" class="text-center text-gray-500">Loading ticket details...</div>
      <div v-if="error" class="p-4 text-red-700 bg-red-100 border border-red-400 rounded">
        Error loading ticket: {{ error }}
      </div>

      <div v-if="ticket && !loading && !error" class="p-6 bg-white rounded-lg shadow-md">
        <div class="pb-6 mb-6 border-b border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h1 class="text-3xl font-bold text-gray-800">{{ ticket.subject }}</h1>
            <span :class="getStatusClass(ticket.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
              {{ ticket.status }}
            </span>
          </div>
          <p class="mb-4 text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
          <div class="grid grid-cols-1 gap-4 text-sm text-gray-600 md:grid-cols-3">
            <div><strong>Category:</strong> {{ ticket.category }}</div>
            <div><strong>Priority:</strong> {{ ticket.priority }}</div>
            <div><strong>Created:</strong> {{ formatDate(ticket.created_at) }}</div>
            <div><strong>Last Updated:</strong> {{ formatDate(ticket.updated_at) }}</div>
            <div v-if="ticket.user"><strong>Reported by:</strong> {{ ticket.user.name }}</div>
          </div>
          <div v-if="ticket.media && ticket.media.length > 0" class="mt-4">
              <h3 class="text-sm font-medium text-gray-700">Attachments:</h3>
              <ul>
                  <li v-for="file in ticket.media" :key="file.id">
                      <a :href="file.original_url" target="_blank" class="text-indigo-600 hover:text-indigo-800 hover:underline">{{ file.file_name }}</a>
                  </li>
              </ul>
          </div>
        </div>

        <div v-if="isAdmin" class="pb-6 mb-6 border-b border-gray-200">
          <h3 class="mb-2 text-lg font-semibold text-gray-800">Update Status</h3>
          <div class="flex items-center space-x-3">
            <select v-model="newStatus" class="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
            <button @click="updateTicketStatus" :disabled="updatingStatus" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50">
              <span v-if="updatingStatus">Updating...</span>
              <span v-else>Update Status</span>
            </button>
          </div>
          <p v-if="statusUpdateError" class="mt-2 text-xs text-red-600">{{ statusUpdateError }}</p>
          <p v-if="statusUpdateSuccess" class="mt-2 text-xs text-green-600">{{ statusUpdateSuccess }}</p>
        </div>

        <!-- Comments Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-xl font-semibold text-gray-800">Comments ({{ commentCount }})</h3>
          <div v-if="commentsLoading" class="text-sm text-gray-500">Loading comments...</div>
          <div v-if="commentsError" class="text-sm text-red-500">{{ commentsError }}</div>
          <div class="space-y-4">
            <div v-for="comment in safeComments" :key="comment.id" class="p-4 border border-gray-200 rounded-md bg-gray-50">
              <p class="text-gray-700 whitespace-pre-wrap">{{ comment.content }}</p>
              <p class="mt-2 text-xs text-gray-500">
                By {{ comment.user?.name || 'User' }} on {{ formatDate(comment.created_at) }}
              </p>
            </div>
            <div v-if="!commentsLoading && safeComments.length === 0 && !commentsError" class="text-sm text-gray-500">
              No comments yet.
            </div>
          </div>
        </div>

        <!-- Add Comment Form -->
        <div>
          <h3 class="mb-2 text-lg font-semibold text-gray-800">Add a Comment</h3>
          <form @submit.prevent="submitComment">
            <textarea
              v-model="newComment"
              rows="3"
              required
              class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Type your comment here..."
            ></textarea>
            <div v-if="commentSubmitError" class="mt-1 text-xs text-red-600">{{ commentSubmitError }}</div>
            <button
              type="submit"
              :disabled="submittingComment"
              class="px-4 py-2 mt-3 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50"
            >
              <span v-if="submittingComment">Submitting...</span>
              <span v-else>Add Comment</span>
            </button>
          </form>
        </div>

          <!-- Chat Section -->
          <div class="pt-6 mt-6 border-t border-gray-200">
              <h3 class="mb-4 text-xl font-semibold text-gray-800">Chat</h3>

              <div v-if="chatError" class="p-3 mb-3 text-sm text-red-700 bg-red-100 border border-red-300 rounded">
                  {{ chatError }}
              </div>

              <!-- Message Display Area -->
              <div ref="chatContainer" class="p-4 mb-4 overflow-y-auto border border-gray-300 rounded-md h-64 bg-gray-50">
                  <div v-if="chatLoading" class="text-sm text-center text-gray-500">Loading chat...</div>
                  <div v-if="!chatLoading && chatMessages.length === 0 && !chatError" class="text-sm text-center text-gray-400">No messages yet. Start the conversation!</div>
                  <div v-for="message in chatMessages" :key="message.id" class="mb-3">
                      <div :class="[
                          'p-3 rounded-lg max-w-[80%]',
                          message.user_id === currentUserId ? 'bg-indigo-100 text-indigo-900 ml-auto' : 'bg-gray-200 text-gray-800 mr-auto'
                      ]">
                          <p class="text-sm">{{ message.body || message.message }}</p>
                          <p class="mt-1 text-xs opacity-75" :class="message.user_id === currentUserId ? 'text-right' : 'text-left'">
                              {{ message.user?.name || 'User' }} - {{ formatDate(message.created_at) }}
                          </p>
                      </div>
                  </div>
              </div>

              <!-- Message Input Form -->
              <form @submit.prevent="sendChatMessage" class="flex items-center space-x-2">
                  <input
                      type="text"
                      v-model="chatInput"
                      :disabled="sendingMessage || chatLoading || !!chatError"
                      placeholder="Type your message..."
                      required
                      class="flex-grow px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-100"
                  />
                  <button
                      type="submit"
                      :disabled="sendingMessage || !chatInput.trim() || chatLoading || !!chatError"
                      class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                  >
                      <span v-if="sendingMessage">Sending...</span>
                      <span v-else>Send</span>
                  </button>
              </form>
          </div>
      </div>
       <div class="mt-6">
          <router-link :to="{ name: 'tickets.index' }" class="text-indigo-600 hover:text-indigo-800 hover:underline">
              &larr; Back to Tickets
          </router-link>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, onMounted, computed, onUnmounted, nextTick } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import axios from 'axios';
  import { useAuth } from '../../store/auth';

  const route = useRoute();
  const router = useRouter();
  const { user } = useAuth();
  const ticket = ref(null);
  const comments = ref([]);
  const loading = ref(true);
  const error = ref(null);
  const commentsLoading = ref(true);
  const commentsError = ref(null);
  const newComment = ref('');
  const submittingComment = ref(false);
  const commentSubmitError = ref('');

  const newStatus = ref('');
  const updatingStatus = ref(false);
  const statusUpdateError = ref('');
  const statusUpdateSuccess = ref('');

  // Chat state
  const chatMessages = ref([]);
  const chatInput = ref('');
  const chatLoading = ref(true);
  const chatError = ref(null);
  const sendingMessage = ref(false);
  const echoChannel = ref(null);

  const ticketId = computed(() => route.params.id);
  const isAdmin = computed(() => user.value?.roles?.some(role => role.name === 'admin'));
  const currentUserId = computed(() => user.value?.id);

  const safeComments = computed(() => {
      return Array.isArray(comments.value) ? comments.value : [];
  });

  const commentCount = computed(() => safeComments.value.length);

  const fetchTicketDetails = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/tickets/${ticketId.value}`);
      if (response.data && response.data.ticket) {
        ticket.value = response.data.ticket;
        newStatus.value = ticket.value.status;
      } else {
        console.error('Failed to fetch ticket details: Unexpected API response format', response);
        console.log('API Response Data:', response.data);
        error.value = 'Received unexpected data format from the server.';
        ticket.value = null;
      }
    } catch (err) {
      console.error('Failed to fetch ticket details:', err);
      error.value = err.response?.data?.message || err.message || 'Could not load ticket details.';
    } finally {
      loading.value = false;
    }
  };

  const fetchComments = async () => {
    commentsLoading.value = true;
    commentsError.value = null;
    try {
      const response = await axios.get(`/api/tickets/${ticketId.value}/comments`);
      if (response.data && Array.isArray(response.data.comments)) {
        comments.value = response.data.comments;
      } else {
        console.error('Failed to fetch comments: Unexpected API response format', response);
        console.log('Comments API Response Data:', response.data);
        commentsError.value = 'Received unexpected data format for comments.';
        comments.value = [];
      }
    } catch (err) {
      console.error('Failed to fetch comments:', err);
      commentsError.value = err.response?.data?.message || err.message || 'Could not load comments.';
      comments.value = [];
    } finally {
      commentsLoading.value = false;
    }
  };

  const submitComment = async () => {
    if (!newComment.value.trim()) return;
    submittingComment.value = true;
    commentSubmitError.value = '';
    try {
      const response = await axios.post(`/api/tickets/${ticketId.value}/comments`, {
        content: newComment.value,
      });
      console.log('Comment submission response data:', response.data);
      if (response.data && response.data.comment) {
          comments.value.push(response.data.comment);
          console.log('Comments after push:', comments.value);
          newComment.value = '';
      } else {
          console.error('Failed to submit comment: Unexpected API response format', response);
          commentSubmitError.value = 'Received unexpected data format after submitting comment.';
      }
    } catch (err) {
      console.error('Failed to submit comment:', err);
      commentSubmitError.value = err.response?.data?.message || 'Failed to post comment.';
    } finally {
      submittingComment.value = false;
    }
  };

  const updateTicketStatus = async () => {
      if (!newStatus.value) return;
      updatingStatus.value = true;
      statusUpdateError.value = '';
      statusUpdateSuccess.value = '';
      try {
          const response = await axios.patch(`/api/tickets/${ticketId.value}/status`, {
              status: newStatus.value
          });
          ticket.value.status = response.data.ticket.status;
          statusUpdateSuccess.value = 'Status updated successfully!';
          setTimeout(() => statusUpdateSuccess.value = '', 3000);
      } catch (err) {
          console.error('Failed to update status:', err);
          statusUpdateError.value = err.response?.data?.message || 'Failed to update status.';
          setTimeout(() => statusUpdateError.value = '', 3000);
      } finally {
          updatingStatus.value = false;
      }
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };

  const getStatusClass = (status) => {
    switch (status?.toLowerCase()) {
      case 'open': return 'bg-blue-100 text-blue-800';
      case 'in_progress': return 'bg-yellow-100 text-yellow-800';
      case 'resolved': return 'bg-green-100 text-green-800';
      case 'closed': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-200 text-gray-700';
    }
  };

  const fetchChatMessages = async () => {
      chatLoading.value = true;
      chatError.value = null;
      try {
          const response = await axios.get(`/api/tickets/${ticketId.value}/chats`);
          chatMessages.value = response.data.data || [];
          scrollToBottom();
      } catch (err) {
          console.error('Failed to fetch chat messages:', err);
          chatError.value = err.response?.data?.message || 'Could not load chat history.';
      } finally {
          chatLoading.value = false;
      }
  };

  const sendChatMessage = async () => {
      if (!chatInput.value.trim()) return;
      sendingMessage.value = true;
      try {
          await axios.post(`/api/tickets/${ticketId.value}/chats`, {
              body: chatInput.value,
          });
          chatInput.value = '';
      } catch (err) {
          console.error('Failed to send chat message:', err);
          chatError.value = 'Failed to send message.';
          setTimeout(() => chatError.value = null, 3000);
      } finally {
          sendingMessage.value = false;
      }
  };

  const joinChatChannel = () => {
      if (!window.Echo) {
          console.error('Laravel Echo not initialized.');
          chatError.value = 'Chat service connection failed.';
          return;
      }

      const channelName = `chat.${ticketId.value}`;
      console.log(`Attempting to join private channel: ${channelName}`);

      try {
          echoChannel.value = window.Echo.private(channelName)
              .listen('.message.sent', (event) => {
                  console.log('Message received:', event);
                  if (event.chat && event.chat.body) {
                      chatMessages.value.push(event.chat);
                      scrollToBottom();
                  }
              })
              .error((error) => {
                  console.error(`Error subscribing to channel ${channelName}:`, error);
                  if (error.status === 403) {
                      chatError.value = 'You do not have permission to join this chat.';
                  } else {
                      chatError.value = 'Could not connect to chat service.';
                  }
              });
      } catch (error) {
          console.error('Echo channel join error:', error);
          chatError.value = 'Failed to join chat channel.';
      }
  };

  const leaveChatChannel = () => {
      if (echoChannel.value) {
          console.log('Leaving chat channel...');
          echoChannel.value.stopListening('.message.sent');
          window.Echo.leave(`chat.${ticketId.value}`);
          echoChannel.value = null;
      }
  };

  const chatContainer = ref(null);
  const scrollToBottom = () => {
      nextTick(() => {
          const container = chatContainer.value;
          if (container) {
              container.scrollTop = container.scrollHeight;
          }
      });
  };

  onMounted(async () => {
      fetchTicketDetails();
      fetchComments();
      fetchChatMessages();
      joinChatChannel();
  });

  onUnmounted(() => {
      leaveChatChannel();
  });
  </script>
