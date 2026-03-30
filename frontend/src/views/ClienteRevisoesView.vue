<template>
  <AppLayout :titulo="`Revisões — ${clienteNome}`">
    <div class="px-2">

      <!-- Barra de pesquisa + botão + voltar -->
      <div class="flex items-center gap-3 mb-6 pb-6">
        <RouterLink :to="`/clientes/${clienteId}/veiculos?nome=${clienteNome}`"
          class="text-blue-600 hover:underline text-sm whitespace-nowrap">
          ← Voltar
        </RouterLink>
        <input v-model="pesquisa" type="text" placeholder="Pesquisar por descrição..."
          class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        <button @click="mostraCadastrar = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition whitespace-nowrap">
          + Nova revisão
        </button>
      </div>

      <!-- Modal cadastro -->
      <div v-if="mostraCadastrar"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        @click.self="mostraCadastrar = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-gray-800 text-lg mb-4">Nova Revisão</h3>
          <CadastrarRevisao @salvar="criarRevisao" @cancelar="mostraCadastrar = false" />
        </div>
      </div>

      <!-- Modal edição -->
      <div v-if="mostraModificar"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        @click.self="mostraModificar = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-gray-800 text-lg mb-4">Editar Revisão</h3>
          <ModificarRevisao :revisao="revisaoSelecionada" @salvar="salvarModificacao" @cancelar="mostraModificar = false" />
        </div>
      </div>

      <p v-if="revisaoStore.carregando" class="text-gray-500 text-sm">Carregando...</p>

      <template v-else>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6">
          <div v-for="r in revisaoStore.revisoes" :key="r.id"
            class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3">

            <div class="flex items-start justify-between">
              <div>
                <p class="font-bold text-gray-800 text-base">{{ formatarData(r.data_revisao) }}</p>
                <p class="text-xs text-gray-400">{{ r.km_revisao }} km</p>
              </div>
              <span class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full border border-purple-100">
                {{ formatarPreco(r.preco_total) }}
              </span>
            </div>

            <p v-if="r.descricao" class="text-sm text-gray-600">{{ r.descricao }}</p>
            <p v-else class="text-sm text-gray-400 italic">Sem descrição</p>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
              <button @click="abrirModificar(r)" class="text-sm text-gray-500 hover:text-blue-600 transition">
                Editar
              </button>
              <button @click="excluirRevisao(r.id)" class="text-sm text-gray-500 hover:text-red-500 transition ml-auto">
                Excluir
              </button>
            </div>
          </div>
        </div>

        <p v-if="revisaoStore.revisoes.length === 0" class="text-gray-400 text-sm text-center py-8">
          Nenhuma revisão encontrada.
        </p>

        <!-- Paginação -->
        <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-4 py-3">
          <span class="text-sm text-gray-500">
            Exibindo <span class="font-medium text-gray-800">{{ revisaoStore.revisoes.length }}</span> de
            <span class="font-medium text-gray-800">{{ revisaoStore.total }}</span> revisões
          </span>
          <div class="flex items-center gap-2">
            <button
              @click="mudarPagina(revisaoStore.paginaAtual - 1)"
              :disabled="revisaoStore.paginaAtual === 1"
              class="nav px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 transition"
            >
              ← Anterior
            </button>
            <span class="text-sm text-gray-600">{{ revisaoStore.paginaAtual }} / {{ revisaoStore.ultimaPagina }}</span>
            <button
              @click="mudarPagina(revisaoStore.paginaAtual + 1)"
              :disabled="revisaoStore.paginaAtual === revisaoStore.ultimaPagina"
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
import { useRoute } from 'vue-router'
import { useRevisaoStore } from '../stores/revisoes.js'
import { formatarData, formatarPreco } from '../utils/formatadores.js'
import AppLayout from '../components/AppLayout.vue'
import CadastrarRevisao from '../components/forms/cadastrar/CadastrarRevisao.vue'
import ModificarRevisao from '../components/forms/modificar/ModificarRevisao.vue'

const route = useRoute()
const clienteId = route.params.clienteId
const veiculoId = route.params.veiculoId
const clienteNome = route.query.nome
const mostraCadastrar = ref(false)
const mostraModificar = ref(false)
const revisaoSelecionada = ref(null)
const pesquisa = ref('')
let timeoutId = null
const revisaoStore = useRevisaoStore()

onMounted(() => revisaoStore.buscarDoVeiculo(clienteId, veiculoId))

watch(pesquisa, (novoValor) => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    revisaoStore.buscarDoVeiculo(clienteId, veiculoId, 1, novoValor)
  }, 400)
})

function mudarPagina(novaPagina) {
  revisaoStore.buscarDoVeiculo(clienteId, veiculoId, novaPagina, pesquisa.value)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function criarRevisao(form) {
  await revisaoStore.criar(clienteId, veiculoId, form)
  mostraCadastrar.value = false

  revisaoStore.buscarDoVeiculo(clienteId, veiculoId)
}

function abrirModificar(revisao) {
  revisaoSelecionada.value = revisao
  mostraModificar.value = true
}

// async function salvarModificacao(form) {
//   await revisaoStore.atualizar(clienteId, veiculoId, revisaoSelecionada.value.id, form)
//   mostraModificar.value = false
// }

async function salvarModificacao(form) {
  await revisaoStore.atualizar(clienteId, veiculoId, revisaoSelecionada.value.id, form)
  mostraModificar.value = false
  // Força uma atualização da lista para garantir que nada fique undefined
  revisaoStore.buscarDoVeiculo(clienteId, veiculoId, revisaoStore.paginaAtual, pesquisa.value)
}

async function excluirRevisao(id) {
  await revisaoStore.excluir(clienteId, veiculoId, id)
}
</script>