import { ref, computed } from 'vue';
import axios from 'axios';
import router from '../router';

const user = ref(null);
const isAuthenticated = computed(() => !!user.value);
const checkAttempted = ref(false);
const isAuthenticating = ref(false);
const authLoading = ref(false);

async function fetchUser() {
    if (isAuthenticating.value) {
        console.log('Authentication already in progress, skipping duplicate request');
        return user.value;
    }

    isAuthenticating.value = true;
    authLoading.value = true;

    try {
        const accessToken = localStorage.getItem('access_token');
        const tokenType = localStorage.getItem('token_type') || 'Bearer';

        if (!accessToken) {
            console.log('No access token found in storage, user is not authenticated');
            throw new Error('No access token');
        }

        const authHeader = `${tokenType} ${accessToken}`;

        console.log('Attempting to fetch user data with token...');
        const response = await axios.get('/api/user', {
            headers: {
                'Authorization': authHeader
            }
        });

        if (response.data) {
            user.value = response.data;
            console.log('User data fetched successfully:', user.value);
            localStorage.setItem('userData', JSON.stringify(user.value));
            localStorage.setItem('authToken', 'loggedIn');

            checkAttempted.value = true;
            isAuthenticating.value = false;
            authLoading.value = false;
            return user.value;
        } else {
            throw new Error('Invalid response from server: no user data');
        }
    } catch (error) {
        console.log('Failed to fetch user or user not authenticated:', error);
        user.value = null;
        localStorage.removeItem('userData');
        localStorage.removeItem('authToken');

        if (error.response && error.response.status === 401) {
            localStorage.removeItem('access_token');
            localStorage.removeItem('token_type');
            delete axios.defaults.headers.common['Authorization'];
        }

        checkAttempted.value = true;
        isAuthenticating.value = false;
        authLoading.value = false;
        return null;
    }
}

async function login(credentials) {
    if (authLoading.value) {
        console.log('Auth operation already in progress');
        return null;
    }

    authLoading.value = true;

    try {
        console.log('Getting fresh CSRF cookie before login');
        await axios.get('/sanctum/csrf-cookie');
        console.log('CSRF cookie obtained');

        console.log('Attempting login with credentials...');
        const response = await axios.post('/api/login', credentials);

        if (response.data && response.data.access_token) {
            console.log('Login successful, received access token');

            localStorage.setItem('access_token', response.data.access_token);
            localStorage.setItem('token_type', response.data.token_type || 'Bearer');

            axios.defaults.headers.common['Authorization'] =
                `${response.data.token_type || 'Bearer'} ${response.data.access_token}`;

            if (response.data.user) {
                setUserData(response.data.user);
            }

            console.log('Fetching user data with token after login');
            await fetchUser();

            authLoading.value = false;
            return user.value;
        } else {
            throw new Error('Login successful but no access token returned');
        }
    } catch (error) {
        console.error('Login failed:', error);
        authLoading.value = false;
        throw error;
    }
}

function setUserData(userData) {
    if (!userData) {
        console.error('Attempted to set empty user data');
        return;
    }

    user.value = userData;
    checkAttempted.value = true;
    localStorage.setItem('userData', JSON.stringify(userData));
    localStorage.setItem('authToken', 'loggedIn');
    console.log('User data set after login:', user.value);
}

// handle logout
async function logout() {
    if (authLoading.value) {
        console.log('Auth operation already in progress');
        return;
    }

    authLoading.value = true;

    try {
        const accessToken = localStorage.getItem('access_token');

        if (accessToken) {
            console.log('Logging out user with token...');
            await axios.post('/api/logout', {}, {
                headers: {
                    'Authorization': `${localStorage.getItem('token_type') || 'Bearer'} ${accessToken}`
                }
            });
            console.log('Logout API call successful');
        }
    } catch (error) {
        console.error('Logout API call failed:', error);
    } finally {
        user.value = null;
        checkAttempted.value = false;

        delete axios.defaults.headers.common['Authorization'];

        localStorage.removeItem('userData');
        localStorage.removeItem('authToken');
        localStorage.removeItem('access_token');
        localStorage.removeItem('token_type');

        console.log('User logged out, state cleared.');

        authLoading.value = false;

        await router.push({ name: 'login' });
    }
}

function loadUserFromStorage() {
    if (user.value) {
        return;
    }

    const storedUser = localStorage.getItem('userData');
    const authToken = localStorage.getItem('authToken');
    const accessToken = localStorage.getItem('access_token');
    const tokenType = localStorage.getItem('token_type') || 'Bearer';

    if (accessToken) {
        console.log('Restoring access token from storage');
        axios.defaults.headers.common['Authorization'] = `${tokenType} ${accessToken}`;
    }

    if (storedUser && authToken) {
        try {
            user.value = JSON.parse(storedUser);
            console.log('User loaded from storage:', user.value);
        } catch (e) {
            console.error('Failed to parse stored user data:', e);
            localStorage.removeItem('userData');
            localStorage.removeItem('authToken');
        }
    }
}

loadUserFromStorage();

export function useAuth() {
    return {
        user,
        isAuthenticated,
        fetchUser,
        setUserData,
        login,
        logout,
        checkAttempted,
        authLoading
    };
}
