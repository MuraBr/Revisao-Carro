<template>
  <AppLayout titulo="Veículos">
    <div class="px-2">
      <!-- Pesquisa -->
      <div class="flex items-center gap-3 mb-6 pb-6">
        <input v-model="pesquisa" type="text" placeholder="Pesquisar por marca, modelo ou placa..."
          class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>

      <!-- Modal edição -->
      <div v-if="mostraModificar"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        @click.self="mostraModificar = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="font-bold text-gray-800 text-lg mb-4">Editar Veículo</h3>
          <ModificarVeiculo :veiculo="veiculoSelecionado" @salvar="salvarModificacao" @cancelar="mostraModificar = false" />
        </div>
      </div>

      <p v-if="veiculoStore.carregando" class="text-gray-500 text-sm">Carregando...</p>

      <template v-else>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6">
          <div v-for="v in veiculoStore.veiculos" :key="v.id"
            class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3">

            <div class="flex items-start justify-between">
              <div>
                <p class="font-bold text-gray-800 text-base">{{ v.marca }} {{ v.modelo }}</p>
                <p class="text-xs text-gray-400">{{ v.ano }} · {{ v.cor }}</p>
              </div>
              <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full border border-green-100">
                {{ v.placa }}
              </span>
            </div>

            <p class="text-sm text-gray-600">
              Proprietário: <span class="font-medium text-gray-800">{{ v.cliente?.nome }}</span>
            </p>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
              <RouterLink :to="`/clientes/${v.cliente_id}/veiculos/${v.id}/revisoes?nome=${v.cliente?.nome}`"
                class="text-sm text-blue-600 hover:underline font-medium">
                Ver revisões
              </RouterLink>
              <button @click="abrirModificar(v)" class="text-sm text-gray-500 hover:text-blue-600 transition">
                Editar
              </button>
              <button @click="excluirVeiculo(v.id)" class="text-sm text-gray-500 hover:text-red-500 transition ml-auto">
                Excluir
              </button>
            </div>
          </div>
        </div>
        <p v-if="veiculoStore.veiculos.length === 0" class="text-gray-400 text-sm text-center py-8">
          Nenhum veículo encontrado.
        </p>
        <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-4 py-3">
          <span class="text-sm text-gray-500">
            Exibindo <span class="font-medium text-gray-800">{{ veiculoStore.veiculos.length }}</span> de
            <span class="font-medium text-gray-800">{{ veiculoStore.total }}</span> veículos
          </span>
          <div class="flex items-center gap-2">
            <button
              @click="mudarPagina(veiculoStore.paginaAtual - 1)"
              :disabled="veiculoStore.paginaAtual === 1"
              class="nav px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 transition"
            >
              ← Anterior
            </button>
            <span class="text-sm text-gray-600">{{ veiculoStore.paginaAtual }} / {{ veiculoStore.ultimaPagina }}</span>
            <button
              @click="mudarPagina(veiculoStore.paginaAtual + 1)"
              :disabled="veiculoStore.paginaAtual === veiculoStore.ultimaPagina"
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
import { useVeiculoStore } from '../stores/veiculos.js'
import AppLayout from '../components/AppLayout.vue'
import ModificarVeiculo from '../components/forms/modificar/ModificarVeiculo.vue'

const mostraModificar = ref(false)
const veiculoSelecionado = ref(null)
const pesquisa = ref('')
const veiculoStore = useVeiculoStore()
let timeoutId = null

watch(pesquisa, (novoValor) => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    veiculoStore.buscarTodos(1, novoValor)
  }, 400)
})

onMounted(() => veiculoStore.buscarTodos())

async function mudarPagina(novaPagina) {
  await veiculoStore.buscarTodos(novaPagina)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function abrirModificar(veiculo) {
  veiculoSelecionado.value = veiculo
  mostraModificar.value = true
}

async function salvarModificacao(form) {
  await veiculoStore.atualizar(veiculoSelecionado.value.cliente_id, veiculoSelecionado.value.id, form)
  mostraModificar.value = false
}

async function excluirVeiculo(id) {
  if (confirm('Deseja realmente excluir este veículo?')) {
    await veiculoStore.excluir(id)
  }
}
</script>