<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow w-full max-w-md">

      <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">
        Criar conta
      </h1>

      <div v-if="erros" class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4">
        <p v-for="(msgs, campo) in erros" :key="campo">{{ msgs[0] }}</p>
      </div>

      <form @submit.prevent="enviar" class="flex flex-col gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Nome</label>
          <input v-model="form.name" type="text" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">E-mail</label>
          <input v-model="form.email" type="email" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Gênero</label>
          <select v-model="form.genero" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Selecione</option>
            <option value="M">Masculino</option>
            <option value="F">Feminino</option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Data de nascimento</label>
          <input v-model="form.data_nascimento" type="date" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Senha</label>
          <input v-model="form.password" type="password" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Confirmar senha</label>
          <input v-model="form.password_confirmation" type="password" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>

        <button type="submit" :disabled="auth.loading"
          class="bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 transition">
          {{ auth.loading ? 'Cadastrando...' : 'Criar conta' }}
        </button>
      </form>

      <p class="text-sm text-center text-gray-500 mt-4">
        Já tem conta?
        <RouterLink to="/login" class="text-blue-600 hover:underline">Entrar</RouterLink>
      </p>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const erros = ref(null)
const form = reactive({
  name: '',
  email: '',
  genero: '',
  data_nascimento: '',
  password: '',
  password_confirmation: '',
})

async function enviar() {
  erros.value = null
  try {
    await auth.register(form)
  } catch (e) {
    erros.value = e.response?.data?.errors ?? null
  }
}
</script>