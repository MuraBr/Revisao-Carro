<template>
  <div class="cadastrar-cliente">
    <form @submit.prevent="submeter">
      <div class="form-group">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" v-model="form.nome" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label for="cpf">CPF:</label>
        <input
          type="text"
          id="cpf"
          :value="form.cpf"
          @input="aoDigitarCpf"
          @keyup="aoDigitarCpf"
          required
          placeholder="000.000.000-00"
          maxlength="14"
          :class="['w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2', erros.cpf ? 'border-red-500 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-400']"
        />
        <span v-if="erros.cpf" class="text-red-500 text-xs mt-1 block">{{ erros.cpf }}</span>
      </div>
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" v-model="form.email" required
          :class="['w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2', erros.email ? 'border-red-500 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-400']" />
        <span v-if="erros.email" class="text-red-500 text-xs mt-1 block">{{ erros.email }}</span>
      </div>
      <div class="form-group">
        <label for="telefone">Telefone:</label>
        <input
          type="text"
          id="telefone"
          :value="form.telefone"
          @input="aoDigitarTelefone"
          @keyup="aoDigitarTelefone"
          placeholder="(00) 00000-0000"
          maxlength="15"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label for="cep">CEP:</label>
        <input
          type="text"
          id="cep"
          :value="form.cep"
          @input="aoDigitarCep"
          @keyup="aoDigitarCep"
          @blur="buscaCep"
          placeholder="00000-000"
          maxlength="9"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>
      <div class="form-group">
        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" v-model="form.endereco" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label for="numero">Número:</label>
        <input type="text" id="numero" maxlength="10" v-model="form.numero" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" v-model="form.data_nascimento" :max="dataHoje" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label class="block text-sm font-medium text-gray-700 mb-1">Gênero:</label>
        
        <div class="flex items-center space-x-4">
          <label class="flex items-center cursor-pointer text-sm text-gray-600 pr-3">
            <input 
              type="radio" 
              v-model="form.genero" 
              value="M" 
              class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
            />
            <span class="ml-2 pl-1">Masculino</span>
          </label>

          <label class="flex items-center cursor-pointer text-sm text-gray-600">
            <input 
              type="radio" 
              v-model="form.genero" 
              value="F" 
              class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
            />
            <span class="ml-2 pl-1">Feminino</span>
          </label>
        </div>
      </div>
      <div class="flex justify-end space-x-2 pt-2">
        <button type="button" @click="emit('cancelar')"
          class="px-4 py-2 text-sm text-gray-600 hover:underline">
          Cancelar
        </button>
        <button type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
          Salvar
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useClienteStore } from '../../../stores/clientes.js'
import axios from 'axios'

const clienteStore = useClienteStore()

const dataHoje = new Date().toISOString().split('T')[0];

// define os eventos que este componente pode emitir
const emit = defineEmits(['salvar', 'cancelar'])

const erros = reactive({
  cpf: '',
  email: ''
})

const form = reactive({
  nome: '',
  cpf: '',
  email: '',
  telefone: '',
  endereco: '',
  numero: '',
  cep: '',
  data_nascimento: '',
  genero: '',
})

async function buscaCep() {
  const resultado = await clienteStore.buscarCep(form.cep)
  if (resultado) {
    form.endereco = resultado.endereco
    form.cep = resultado.cep
  }
}

function aoDigitarCpf(e) {
  form.cpf = clienteStore.formatarCpf(e.target.value)
}

function aoDigitarCep(e) {
  form.cep = clienteStore.formatarCep(e.target.value)
}

function aoDigitarTelefone(e) {
  form.telefone = clienteStore.formatarTelefone(e.target.value)
}

async function submeter() {
  // Limpa erros anteriores antes de validar
  erros.cpf = ''
  erros.email = ''

  try {
    // Faz a checagem no backend usando as rotas que você criou no api.php
    const [resCpf, resEmail] = await Promise.all([
      axios.get('/api/clientes/verificar-cpf', { params: { cpf: form.cpf } }),
      axios.get('/api/clientes/verificar-email', { params: { email: form.email } })
    ])

    if (resCpf.data.existe) erros.cpf = 'Este CPF já está cadastrado.'
    if (resEmail.data.existe) erros.email = 'Este email já está cadastrado.'

    // Se houver mensagens de erro, para a execução aqui
    if (erros.cpf || erros.email) return

    // Se estiver tudo ok, emite o evento para o componente pai (ClientesView)
    emit('salvar', form)
  } catch (error) {
    console.error("Erro na validação:", error)
  }
}

// function submeter() {
//   emit('salvar', form)  // envia os dados para a view pai
// }
</script>