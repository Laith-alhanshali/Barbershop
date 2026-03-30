import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../pages/Dashboard.vue';
import Appointments from '../pages/Appointments.vue';
import Invoices from '../pages/Invoices.vue';

const routes = [
    {
        path: '/',
        name: 'Dashboard',
        component: Dashboard,
        meta: { title: 'لوحة التحكم' } // Dashboard
    },
    {
        path: '/appointments',
        name: 'Appointments',
        component: Appointments,
        meta: { title: 'المواعيد' } // Appointments
    },
    {
        path: '/invoices',
        name: 'Invoices',
        component: Invoices,
        meta: { title: 'الفواتير' } // Invoices
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
