<template>
  <AppLayout titulo="Todas as Revisões">
    <div class="px-2">
      <!-- Pesquisa -->
      <div class="flex items-center gap-3 mb-6 pb-6">
        <input v-model="pesquisa" type="text" placeholder="Pesquisar por veículo, cliente ou descrição..."
          class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
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
                <p class="font-bold text-gray-800 text-base">{{ r.veiculo?.marca }} {{ r.veiculo?.modelo }}</p>
                <p class="text-xs text-gray-400">{{ r.veiculo?.placa }} · {{ r.veiculo?.cliente?.nome }}</p>
              </div>
              <span class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full border border-purple-100">
                {{ formatarPreco(r.preco_total) }}
              </span>
            </div>

            <div class="flex flex-col gap-1 text-sm text-gray-600">
              <p>Data: <span class="text-gray-800">{{ formatarData(r.data_revisao) }}</span></p>
              <p>KM: <span class="text-gray-800">{{ r.km_revisao }} km</span></p>
              <p v-if="r.descricao">{{ r.descricao }}</p>
              <p v-else class="text-gray-400 italic">Sem descrição</p>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
              <RouterLink :to="`/clientes/${r.veiculo?.cliente_id}/veiculos/${r.veiculo_id}/revisoes?nome=${r.veiculo?.cliente?.nome}`"
                class="text-sm text-blue-600 hover:underline font-medium">
                Ver detalhes
              </RouterLink>
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
import { useRevisaoStore } from '../stores/revisoes'
import AppLayout from '../components/AppLayout.vue'
import ModificarRevisao from '../components/forms/modificar/ModificarRevisao.vue'
import { formatarData, formatarPreco } from '../utils/formatadores'

const mostraModificar = ref(false)
const revisaoStore = useRevisaoStore()
const revisaoSelecionada = ref(null)
const pesquisa = ref('')
let timeoutId = null

onMounted(() => revisaoStore.buscarTodas())

watch(pesquisa, (novoValor) => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    revisaoStore.buscarTodas(1, novoValor)
  }, 400)
})

async function mudarPagina(novaPagina) {
  await revisaoStore.buscarTodas(novaPagina)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function abrirModificar(revisao) {
  revisaoSelecionada.value = revisao
  mostraModificar.value = true
}

async function salvarModificacao(form) {
  await revisaoStore.atualizar(
    revisaoSelecionada.value.veiculo.cliente_id,
    revisaoSelecionada.value.veiculo_id,
    revisaoSelecionada.value.id,
    form
  )
  mostraModificar.value = false
}

async function excluirRevisao(id) {
  await revisaoStore.excluir(
    revisaoSelecionada.value?.veiculo?.cliente_id,
    revisaoSelecionada.value?.veiculo_id,
    id
  )
}
</script>