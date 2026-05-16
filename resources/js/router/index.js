import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const requireAuth = (to, from, next) => {
  const authStore = useAuthStore();
  if (!authStore.isAuthenticated) next('/login');
  else next();
};

const requireGuest = (to, from, next) => {
  const authStore = useAuthStore();
  if (authStore.isAuthenticated) next('/dashboard');
  else next();
};

const AppLayout     = () => import('@/layouts/AppLayout.vue');
const AuthLayout    = () => import('@/layouts/AuthLayout.vue');
const MinimalLayout = () => import('@/layouts/MinimalLayout.vue');

const Dashboard      = () => import('@/pages/Dashboard.vue');
const Reports        = () => import('@/pages/Reports.vue');
const Contacts       = () => import('@/pages/Contacts.vue');
const Vendors        = () => import('@/pages/Vendors.vue');
const BankAccounts   = () => import('@/pages/BankAccounts.vue');
const Expenses       = () => import('@/pages/Expenses.vue');
const Incomes        = () => import('@/pages/Incomes.vue');
const Payments       = () => import('@/pages/Payments.vue');
const BankTransfers  = () => import('@/pages/BankTransfers.vue');
const Loans          = () => import('@/pages/Loans.vue');
const Items          = () => import('@/pages/Items.vue');
const Purchases      = () => import('@/pages/Purchases.vue');
const CreatePurchase  = () => import('@/pages/CreatePurchase.vue');
const EditPurchase    = () => import('@/pages/EditPurchase.vue');
const Warehouses     = () => import('@/pages/Warehouses.vue');
const Sales          = () => import('@/pages/Sales.vue');
const Settlements    = () => import('@/pages/Settlements.vue');
const JournalVouchers = () => import('@/pages/JournalVouchers.vue');
const CustomAccounts = () => import('@/pages/CustomAccounts.vue');
const SystemSetup    = () => import('@/pages/SystemSetup.vue');
const Settings       = () => import('@/pages/Settings.vue');
const Team           = () => import('@/pages/Team.vue');
const Subscription   = () => import('@/pages/Subscription.vue');
const Login          = () => import('@/pages/auth/Login.vue');
const Register       = () => import('@/pages/auth/Register.vue');
const NotFound       = () => import('@/pages/NotFound.vue');

const appRoute = (path, name, title, component) => ({
  path,
  name,
  component: AppLayout,
  meta: { title, requiresAuth: true },
  beforeEnter: requireAuth,
  children: [{ path: '', component }],
});

const routes = [
  { path: '/', redirect: '/dashboard' },

  appRoute('/dashboard',        'Dashboard',       'Dashboard',        Dashboard),
  appRoute('/reports',          'Reports',         'Reports',          Reports),
  appRoute('/contacts',         'Contacts',        'Contacts',         Contacts),
  appRoute('/vendors',          'Vendors',         'Vendors',          Vendors),
  appRoute('/bank-accounts',    'BankAccounts',    'Bank Accounts',    BankAccounts),
  appRoute('/expenses',         'Expenses',        'Expenses',         Expenses),
  appRoute('/incomes',          'Incomes',         'Incomes',          Incomes),
  appRoute('/payments',         'Payments',        'Payments',         Payments),
  appRoute('/bank-transfers',   'BankTransfers',   'Bank Transfers',   BankTransfers),
  appRoute('/loans',            'Loans',           'Loans & Advances', Loans),
  appRoute('/items',            'Items',           'Items',            Items),
  appRoute('/purchases',        'Purchases',       'Purchases',        Purchases),
  appRoute('/purchases/create', 'CreatePurchase',  'Create Purchase',  CreatePurchase),
  appRoute('/purchases/:id/edit', 'EditPurchase',  'Edit Purchase',    EditPurchase),
  appRoute('/warehouses',       'Warehouses',      'Warehouses',       Warehouses),
  appRoute('/sales',            'Sales',           'Sales',            Sales),
  appRoute('/settlements',      'Settlements',     'Settlements',      Settlements),
  appRoute('/journal-vouchers', 'JournalVouchers', 'Journal Vouchers', JournalVouchers),
  appRoute('/custom-accounts',  'CustomAccounts',  'Custom Accounts',  CustomAccounts),
  appRoute('/system-setup',     'SystemSetup',     'System Setup',     SystemSetup),
  appRoute('/settings',         'Settings',        'Settings',         Settings),
  appRoute('/team',             'Team',            'Team',             Team),
  appRoute('/subscription',     'Subscription',    'Subscription',     Subscription),

  {
    path: '/login',
    component: AuthLayout,
    beforeEnter: requireGuest,
    children: [{ path: '', component: Login }],
  },
  {
    path: '/register',
    component: AuthLayout,
    beforeEnter: requireGuest,
    children: [{ path: '', component: Register }],
  },
  {
    path: '/:pathMatch(.*)*',
    component: MinimalLayout,
    children: [{ path: '', component: NotFound }],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0 };
  },
});

router.beforeEach((to, from, next) => {
  if (to.meta.title) document.title = `${to.meta.title} — Hisaab`;
  next();
});

export default router;
