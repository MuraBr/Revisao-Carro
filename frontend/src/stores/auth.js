import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '../axios'
import router from '../router'

export const useAuthStore = defineStore('auth', () => {

  // ── Estado ────────────────────────────────────────
  const user    = ref(null)
  const loading = ref(false)

  // ── Getters ───────────────────────────────────────
  const isLoggedIn = computed(() => !!user.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')

  // ── Ações ─────────────────────────────────────────
  async function fetchUser() {
    try {
      const { data } = await axios.get('/api/auth/me')
      user.value = data
    } catch {
      user.value = null
    } 
  }

  async function login(credentials) {
    loading.value = true
    try {
      await axios.get('/sanctum/csrf-cookie')
      const { data } = await axios.post('/api/auth/login', credentials)
      user.value = data
      await router.push('/dashboard')
    } finally {
      loading.value = false
    }
  }

  async function register(form) {
    loading.value = true
    try {
      await axios.get('/sanctum/csrf-cookie')
      const { data } = await axios.post('/api/auth/register', form)
      user.value = data
      await router.push('/dashboard')
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    await axios.post('/api/auth/logout')
    user.value = null
    await router.push('/login')
  }

  return { user, loading, isLoggedIn, isAdmin, fetchUser, login, register, logout }
})