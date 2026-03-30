<template>
  <div class="flex min-h-screen w-full bg-gray-50">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 min-h-screen flex flex-col sticky top-0 h-screen">
      
      <!-- Logo -->
      <div class="px-6 py-5 border-b border-gray-100">
        <span class="font-bold text-gray-900 text-xl">RevisãoCarro</span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-4 py-6 flex flex-col gap-1">
        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Menu</p>
        
        <RouterLink to="/dashboard"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition text-sm font-medium"
          active-class="bg-blue-50 text-blue-700 hover:bg-blue-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Dashboard
        </RouterLink>

        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mt-4 mb-2">Cadastros</p>

        <RouterLink to="/clientes"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition text-sm font-medium"
          active-class="bg-blue-50 text-blue-700 hover:bg-blue-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Clientes
        </RouterLink>

        <RouterLink to="/veiculos"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition text-sm font-medium"
          active-class="bg-blue-50 text-blue-700 hover:bg-blue-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-1M13 16H9m4 0h3m3-10l1 4H14V6h2.5L19 6z" />
          </svg>
          Veículos
        </RouterLink>

        <RouterLink to="/revisoes"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition text-sm font-medium"
          active-class="bg-blue-50 text-blue-700 hover:bg-blue-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Revisões
        </RouterLink>
      </nav>

      <!-- Usuário -->
      <div class="px-4 py-4 border-t border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
          {{ auth.user?.name?.charAt(0).toUpperCase() }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 truncate">{{ auth.user?.name }}</p>
        </div>
        <button @click="auth.logout()" title="Sair"
          class="text-gray-400 hover:text-red-500 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>

    </aside>

    <!-- Conteúdo -->
    <div class="flex-1 flex flex-col min-h-screen">

      <!-- Topbar -->
      <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
        <h1 class="text-lg font-bold text-gray-900">{{ titulo }}</h1>
      </header>

      <!-- Slot -->
      <main class="flex-1 p-10 bg-gray-50 overflow-y-auto">
        <slot />
      </main>

    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

defineProps({
  titulo: {
    type: String,
    default: ''
  }
})

const auth = useAuthStore()
</script>