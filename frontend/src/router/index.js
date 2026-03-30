import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Públicas
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
    },

    // Protegidas
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/clientes',
      name: 'clientes',
      component: () => import('../views/ClientesView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/clientes/:clienteId/veiculos',
      name: 'cliente-veiculos',
      component: () => import('../views/ClienteVeiculosView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/clientes/:clienteId/veiculos/:veiculoId/revisoes',
      name: 'cliente-revisoes',
      component: () => import('../views/ClienteRevisoesView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/veiculos',
      name: 'veiculos',
      component: () => import('../views/VeiculosView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/revisoes',
      name: 'revisoes',
      component: () => import('../views/RevisoesView.vue'),
      meta: { requiresAuth: true },
    },

    // Rota desconhecida
    {
      path: '/:pathMatch(.*)*',
      redirect: '/login',
    },
  ],
})

// ── Navigation Guard ──────────────────────────────
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // tenta recuperar usuário da sessão (ex: após F5)
  if (!auth.user) await auth.fetchUser()

  // rota exige login e não está logado -> vai para login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login' }
  }

  // já está logado e tenta acessar login/register -> vai para dashboard
  if (['login', 'register'].includes(to.name) && auth.isLoggedIn) {
    return { name: 'dashboard' }
  }

  // rota exige admin e não é admin -> vai para dashboard
  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'dashboard' }
  }
})

export default router