import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = false;

const accessToken = localStorage.getItem('access_token');
const tokenType = localStorage.getItem('token_type') || 'Bearer';
if (accessToken) {
    console.log('Setting Authorization header from localStorage during bootstrap');
    axios.defaults.headers.common['Authorization'] = `${tokenType} ${accessToken}`;
}

import { useAuth } from './store/auth';
import router from './router';

let isRedirecting = false;
let isRetrying = false;
let retriesCount = 0;
const maxRetries = 1;

axios.interceptors.response.use(
    (response) => {
        retriesCount = 0;
        return response;
    },
    async (error) => {
        if (error.response && error.response.status === 401 && !isRedirecting && !isRetrying && retriesCount < maxRetries) {
            console.log('Interceptor caught 401 error, attempting to retry with token');

            retriesCount++;
            console.log(`Retry attempt: ${retriesCount} of ${maxRetries}`);

            if (!isRetrying) {
                isRetrying = true;

                try {
                    const retryConfig = { ...error.config };
                    retryConfig._retry = true;

                    const accessToken = localStorage.getItem('access_token');
                    const tokenType = localStorage.getItem('token_type') || 'Bearer';
                    if (accessToken) {
                        if (!retryConfig.headers) {
                            retryConfig.headers = {};
                        }
                        console.log('Adding Authorization header for retry request');
                        retryConfig.headers['Authorization'] = `${tokenType} ${accessToken}`;

                        isRetrying = false;
                        return axios(retryConfig);
                    } else {
                        throw new Error('No access token available for retry');
                    }
                } catch (retryError) {
                    isRetrying = false;
                    console.log('Retry failed or no token available, redirecting to login:', retryError);

                    isRedirecting = true;

                    localStorage.removeItem('userData');
                    localStorage.removeItem('authToken');
                    localStorage.removeItem('access_token');
                    localStorage.removeItem('token_type');

                    delete axios.defaults.headers.common['Authorization'];

                    if (router.currentRoute.value.name !== 'login') {
                        await router.push({
                            name: 'login',
                            query: { redirect: router.currentRoute.value.fullPath }
                        });
                    }

                    setTimeout(() => {
                        isRedirecting = false;
                        retriesCount = 0;
                    }, 1000);
                }
            }
        } else if (error.response && error.response.status === 401) {
            if (retriesCount >= maxRetries && !isRedirecting) {
                console.log('Max retries reached, redirecting to login');

                isRedirecting = true;

                localStorage.removeItem('userData');
                localStorage.removeItem('authToken');
                localStorage.removeItem('access_token');
                localStorage.removeItem('token_type');

                delete axios.defaults.headers.common['Authorization'];

                if (router.currentRoute.value.name !== 'login') {
                    await router.push({
                        name: 'login',
                        query: { redirect: router.currentRoute.value.fullPath }
                    });
                }

                setTimeout(() => {
                    isRedirecting = false;
                    retriesCount = 0;
                }, 1000);
            }
        }
        return Promise.reject(error);
    }
);


import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

console.log('VITE_PUSHER_APP_KEY:', import.meta.env.VITE_PUSHER_APP_KEY);

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                axios.post('/api/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    callback(false, response.data);
                })
                .catch(error => {
                    callback(true, error);
                });
            }
        };
    },
});
