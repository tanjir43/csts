import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set Bearer token from localStorage if it exists
const accessToken = localStorage.getItem('access_token');
const tokenType = localStorage.getItem('token_type') || 'Bearer';
if (accessToken) {
    console.log('Setting Authorization header from localStorage during bootstrap');
    axios.defaults.headers.common['Authorization'] = `${tokenType} ${accessToken}`;
}

// Add axios interceptors to handle 401 errors
import { useAuth } from './store/auth';
import router from './router';

// Flag to prevent infinite redirect loops
let isRedirecting = false;
let isRetrying = false;
let retriesCount = 0;
const maxRetries = 1; // Only retry once

// Response interceptor for API calls
axios.interceptors.response.use(
    (response) => {
        // Reset retry counter on successful response
        retriesCount = 0;
        return response;
    },
    async (error) => {
        // Handle 401 Unauthorized errors
        if (error.response && error.response.status === 401 && !isRedirecting && !isRetrying && retriesCount < maxRetries) {
            console.log('Interceptor caught 401 error, attempting to retry with token');

            // Increment retry counter
            retriesCount++;
            console.log(`Retry attempt: ${retriesCount} of ${maxRetries}`);

            // Try to retry the request with token (if available)
            if (!isRetrying) {
                isRetrying = true;

                try {
                    // Retry the original request
                    const retryConfig = { ...error.config };
                    retryConfig._retry = true;

                    // Add Bearer token if available in localStorage but missing in request
                    const accessToken = localStorage.getItem('access_token');
                    const tokenType = localStorage.getItem('token_type') || 'Bearer';
                    if (accessToken) {
                        if (!retryConfig.headers) {
                            retryConfig.headers = {};
                        }
                        console.log('Adding Authorization header for retry request');
                        retryConfig.headers['Authorization'] = `${tokenType} ${accessToken}`;

                        isRetrying = false;
                        // Attempt the retry with token
                        return axios(retryConfig);
                    } else {
                        // No token available, redirect to login
                        throw new Error('No access token available for retry');
                    }
                } catch (retryError) {
                    isRetrying = false;
                    console.log('Retry failed or no token available, redirecting to login:', retryError);

                    // Set flag to prevent multiple redirects
                    isRedirecting = true;

                    // Clear auth state
                    localStorage.removeItem('userData');
                    localStorage.removeItem('authToken');
                    localStorage.removeItem('access_token');
                    localStorage.removeItem('token_type');

                    // Remove token from axios headers
                    delete axios.defaults.headers.common['Authorization'];

                    // Only redirect if we're not already on the login page
                    if (router.currentRoute.value.name !== 'login') {
                        await router.push({
                            name: 'login',
                            query: { redirect: router.currentRoute.value.fullPath }
                        });
                    }

                    // Reset the flags after a short delay
                    setTimeout(() => {
                        isRedirecting = false;
                        retriesCount = 0;
                    }, 1000);
                }
            }
        } else if (error.response && error.response.status === 401) {
            // If we've already retried the maximum number of times, clear auth state and redirect
            if (retriesCount >= maxRetries && !isRedirecting) {
                console.log('Max retries reached, redirecting to login');

                isRedirecting = true;

                // Clear auth state
                localStorage.removeItem('userData');
                localStorage.removeItem('authToken');
                localStorage.removeItem('access_token');
                localStorage.removeItem('token_type');

                // Remove token from axios headers
                delete axios.defaults.headers.common['Authorization'];

                // Only redirect if we're not already on the login page
                if (router.currentRoute.value.name !== 'login') {
                    await router.push({
                        name: 'login',
                        query: { redirect: router.currentRoute.value.fullPath }
                    });
                }

                // Reset the flags after a short delay
                setTimeout(() => {
                    isRedirecting = false;
                    retriesCount = 0;
                }, 1000);
            }
        }
        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Enable Pusher logging for debugging (remove in production)
Pusher.logToConsole = true;

console.log('VITE_PUSHER_APP_KEY:', import.meta.env.VITE_PUSHER_APP_KEY);
console.log('VITE_PUSHER_APP_CLUSTER:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: 80,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    // Add authentication for private channels
    auth: {
        headers: {
            'Authorization': localStorage.getItem('access_token')
                ? `${localStorage.getItem('token_type') || 'Bearer'} ${localStorage.getItem('access_token')}`
                : null,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    },
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                const token = localStorage.getItem('access_token');
                const tokenType = localStorage.getItem('token_type') || 'Bearer';

                console.log('Authorizing channel:', channel.name);
                console.log('Socket ID:', socketId);
                console.log('Has token:', !!token);

                axios.post('/api/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                }, {
                    headers: {
                        'Authorization': token ? `${tokenType} ${token}` : null,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => {
                    console.log('Channel authorization success:', response.data);
                    callback(false, response.data);
                })
                .catch(error => {
                    console.error('Channel authorization failed:', error);
                    console.error('Error details:', error.response?.data);
                    callback(true, error);
                });
            }
        };
    },
});
