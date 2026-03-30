<template>
  <AppLayout titulo="Clientes">
    <div class="px-2">
      <div class="flex items-center gap-3 mb-6 pb-6">
        <input
          v-model="pesquisa"
          type="text"
          placeholder="Pesquisar por nome..."
          class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
        <button @click="mostraCadastrar = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition whitespace-nowrap">
          + Novo cliente
        </button>
      </div>

      <div v-if="mostraCadastrar" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-gray-800 text-lg mb-4">Novo Cliente</h3>
          <CadastrarCliente @salvar="criarCliente" @cancelar="mostraCadastrar = false" />
        </div>
      </div>

      <div v-if="mostraModificar" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-gray-800 text-lg mb-4">Editar Cliente</h3>
          <ModificarCliente :cliente="clienteSelecionado" @salvar="salvarModificacao" @cancelar="mostraModificar = false" />
        </div>
      </div>

      <p v-if="clienteStore.carregando" class="text-gray-500 text-sm">Carregando...</p>

      <template v-else>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6">
          <div v-for="c in clienteStore.clientes" :key="c.id"
            class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3">
            
            <div class="flex items-start justify-between">
              <div>
                <p class="font-bold text-gray-800 text-base">{{ c.nome }}</p>
                <p class="text-xs text-gray-400">
                  {{ c.genero === 'M' ? 'Masculino' : 'Feminino' }} · {{ formatarData(c.data_nascimento) }}
                </p>
              </div>
              <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-100">
                Cliente
              </span>
            </div>

            <div class="flex flex-col gap-1 text-sm text-gray-600">
              <p>CPF: <span class="text-gray-800">{{ c.cpf }}</span></p>
              <p>Email: <span class="text-gray-800">{{ c.email }}</span></p>
              <p>Telefone: <span class="text-gray-800">{{ c.telefone }}</span></p>
              <p>Endereço: <span class="text-gray-800">{{ c.endereco }}, Nº {{ c.numero }}</span></p>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
              <RouterLink :to="`/clientes/${c.id}/veiculos?nome=${c.nome}`"
                class="text-sm text-blue-600 hover:underline font-medium">
                Ver veículos
              </RouterLink>
              <button @click="abrirModificar(c)" class="text-sm text-gray-500 hover:text-blue-600 transition">
                Editar
              </button>
              <button @click="excluirCliente(c.id)" class="text-sm text-gray-500 hover:text-red-500 transition ml-auto">
                Excluir
              </button>
            </div>
          </div>
        </div>

        <p v-if="clienteStore.clientes.length === 0" class="text-gray-400 text-sm text-center py-8">
          Nenhum cliente encontrado.
        </p>

        <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-4 py-3 mt-6">
          <span class="text-sm text-gray-500">
            Exibindo <span class="font-medium text-gray-800">{{ clienteStore.clientes.length }}</span> de
            <span class="font-medium text-gray-800">{{ clienteStore.total }}</span> clientes
          </span>
          <div class="flex items-center gap-2">
            <button 
              @click="mudarPagina(clienteStore.paginaAtual - 1)" 
              :disabled="clienteStore.paginaAtual === 1"
              class="nav px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 transition"
            >
              ← Anterior
            </button>
            <span class="text-sm text-gray-600">{{ clienteStore.paginaAtual }} / {{ clienteStore.ultimaPagina }}</span>
            <button 
              @click="mudarPagina(clienteStore.paginaAtual + 1)" 
              :disabled="clienteStore.paginaAtual === clienteStore.ultimaPagina"
              class="nav px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 transition"
            >
              Próxima →
            </button>
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useClienteStore } from '../stores/clientes.js'
import AppLayout from '../components/AppLayout.vue'
import CadastrarCliente from '../components/forms/cadastrar/CadastrarCliente.vue'
import ModificarCliente from '../components/forms/modificar/ModificarCliente.vue'
import { formatarData } from '../utils/formatadores'

const clienteStore = useClienteStore()
const mostraCadastrar = ref(false)
const mostraModificar = ref(false)
const clienteSelecionado = ref(null)
const pesquisa = ref('')
let timeoutId = null

// Busca inicial
onMounted(() => clienteStore.buscarTodos())

// Função para mudar de página chamando a API
async function mudarPagina(novaPagina) {
  await clienteStore.buscarTodos(novaPagina)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Observa a pesquisa: Quando o usuário digita, buscamos no banco desde a página 1
watch(pesquisa, (novoValor) => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    clienteStore.buscarTodos(1, novoValor)
  }, 400)
})

async function criarCliente(form) {
  await clienteStore.criar(form)
  mostraCadastrar.value = false

  clienteStore.buscarTodos()
}

function abrirModificar(cliente) {
  clienteSelecionado.value = cliente
  mostraModificar.value = true
}

async function salvarModificacao(form) {
  await clienteStore.atualizar(clienteSelecionado.value.id, form)
  mostraModificar.value = false
}

async function excluirCliente(id) {
  if (confirm('Deseja realmente excluir este cliente?')) {
    await clienteStore.excluir(id)
  }
}
</script>