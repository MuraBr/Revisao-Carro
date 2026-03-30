<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-10 rounded-2xl shadow text-center w-full max-w-md">

      <h1 class="text-3xl font-bold text-gray-800 mb-2">RevisãoCarro</h1>
      <p class="text-gray-500 text-sm mb-8">Gerencie as revisões dos seus veículos</p>

      <div class="flex flex-col gap-3">
        <RouterLink to="/login"
          class="bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
          Entrar
        </RouterLink>

        <RouterLink to="/register"
          class="border border-blue-600 text-blue-600 py-2 rounded-lg font-semibold hover:bg-blue-50 transition">
          Criar conta
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>

import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'

const auth = useAuthStore()
const router = useRouter()

// se já estiver logado, redireciona para o dashboard
onMounted(async () => {
  if (!auth.user) await auth.fetchUser()
  if (auth.isLoggedIn) router.push('/dashboard')
})

</script>