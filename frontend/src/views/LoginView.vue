<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow w-full max-w-md">

      <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">
        Entrar no sistema
      </h1>

      <div v-if="erro" class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4">
        {{ erro }}
      </div>

      <form @submit.prevent="enviar" class="flex flex-col gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">E-mail</label>
          <input v-model="form.email" type="email" placeholder="seu@email.com" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Senha</label>
          <input v-model="form.password" type="password" placeholder="••••••••" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>

        <button type="submit" :disabled="auth.loading"
          class="bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 transition">
          {{ auth.loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="text-sm text-center text-gray-500 mt-4">
        Não tem conta?
        <RouterLink to="/register" class="text-blue-600 hover:underline">Cadastre-se</RouterLink>
      </p>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const erro = ref('')
const form = reactive({ email: '', password: '' })

async function enviar() {
  erro.value = ''
  try {
    await auth.login(form)
  } catch (e) {
    erro.value = e.response?.data?.errors?.email?.[0]
      ?? e.response?.data?.message
      ?? 'E-mail ou senha incorretos.'
  }
}
</script>