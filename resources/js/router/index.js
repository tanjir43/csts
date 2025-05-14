import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../components/Auth/Login.vue'),
        meta: { requiresGuest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../components/Auth/Register.vue'),
        meta: { requiresGuest: true }
    },
    {
        path: '/',
        component: () => import('../components/Layouts/DashboardLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'tickets.index',
                component: () => import('../components/Tickets/TicketList.vue')
            },
            {
                path: 'tickets/create',
                name: 'tickets.create',
                component: () => import('../components/Tickets/TicketCreate.vue')
            },
            {
                path: 'tickets/:id',
                name: 'tickets.show',
                component: () => import('../components/Tickets/TicketDetails.vue'),
                props: true
            },
        ]
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

import { useAuth } from '../store/auth';

let isCheckingAuth = false;

router.beforeEach(async (to, from, next) => {
    if (isCheckingAuth) {
        return next();
    }

    const { isAuthenticated, fetchUser, checkAttempted, user } = useAuth();
    console.log('Navigation guard checking auth state:', { isAuthenticated: isAuthenticated.value, user: user.value });

    if (to.matched.some(record => record.meta.requiresAuth) && !isAuthenticated.value) {
        try {
            isCheckingAuth = true;
            console.log('Route requires auth, checking with backend');
            await fetchUser();
            isCheckingAuth = false;

            if (!isAuthenticated.value) {
                console.log('Guard: Auth required, not authenticated. Redirecting to login.');
                return next({
                    name: 'login',
                    query: { redirect: to.fullPath }
                });
            }

            return next();
        } catch (error) {
            isCheckingAuth = false;
            console.log('Auth check failed, redirecting to login');
            return next({
                name: 'login',
                query: { redirect: to.fullPath }
            });
        }
    } else if (to.matched.some(record => record.meta.requiresGuest) && isAuthenticated.value) {
        console.log('Guard: Guest required, authenticated. Redirecting to dashboard.');
        return next({ name: 'tickets.index' });
    } else {
        console.log('Guard: Allowing navigation to:', to.path);
        return next();
    }
});

export default router;
