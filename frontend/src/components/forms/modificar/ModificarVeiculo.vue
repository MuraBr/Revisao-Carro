<template>
  <div class="modificar-veiculo">
    <form @submit.prevent="submeter" class="space-y-4">
      <div class="form-group">
        <label for="placa" class="block text-sm font-medium text-gray-700">Placa:</label>
        <input 
          type="text" 
          id="placa" 
          :value="form.placa" 
          @input="aoDigitarPlaca"
          @keyup="aoDigitarPlaca"
          placeholder="ABC1234"
          maxlength="7"
          required
          :class="[
            'w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2', 
            erros.placa ? 'border-red-500 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-400'
          ]" 
        />
        <span v-if="erros.placa" class="text-red-500 text-xs mt-1 block">
          {{ erros.placa }}
        </span>
      </div>

      <div class="form-group">
        <label for="marca" class="block text-sm font-medium text-gray-700">Marca:</label>
        <select 
          id="marca" 
          v-model="form.marca" 
          @change="aoMudarMarca"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"
        >
          <option value="" disabled>Selecione uma marca</option>
          <option v-for="marca in marcas" :key="marca.codigo" :value="marca.nome">
            {{ marca.nome }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="modelo" class="block text-sm font-medium text-gray-700">Modelo:</label>
        <select 
          id="modelo" 
          v-model="form.modelo" 
          :disabled="!modelos.length"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white disabled:bg-gray-100"
        >
          <option value="" disabled>
            {{ marcas.length ? 'Selecione um modelo' : 'Carregando marcas...' }}
          </option>
          <option v-for="modelo in modelos" :key="modelo.codigo" :value="modelo.nome">
            {{ modelo.nome }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="cor" class="block text-sm font-medium text-gray-700">Cor:</label>
        <select 
          id="cor" 
          v-model="form.cor" 
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"
        >
          <option value="" disabled>Selecione uma cor</option>
          <option v-for="cor in listaCores" :key="cor" :value="cor">
            {{ cor }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="ano" class="block text-sm font-medium text-gray-700">Ano:</label>
        <input type="number" id="ano" v-model="form.ano" min="1900" :max="anoLimite" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
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
import axios from 'axios'
import { onMounted, ref, reactive } from 'vue'
import { useVeiculoStore } from '../../../stores/veiculos'

const veiculoStore = useVeiculoStore()

const marcas = ref([])
const modelos = ref([])
const listaCores = [
  'AMARELO', 'AZUL', 'BRANCO', 'CINZA', 'DOURADO', 
  'LARANJA', 'MARROM', 'PRATA', 'PRETO', 'ROSA', 
  'ROXO', 'VERDE', 'VERMELHO'
]

const anoLimite = new Date().getFullYear() + 1;

onMounted(() => {
  fetchMarcas()
})

const props = defineProps(['veiculo'])
const emit = defineEmits(['salvar', 'cancelar'])

const erros = reactive({
  placa: ''
})

const form = reactive({ ...props.veiculo })

const fetchMarcas = async () => {
  try {
    const response = await fetch('https://parallelum.com.br/fipe/api/v1/carros/marcas')
    marcas.value = await response.json()
  } catch (error) {
    console.error("Erro ao buscar marcas:", error)
  }
}

const aoMudarMarca = async () => {
  // Encontra o ID da marca selecionada pelo nome
  const marcaSelecionada = marcas.value.find(m => m.nome === form.marca)
  if (marcaSelecionada) {
    form.modelo = '' // Limpa o modelo anterior
    await fetchModelos(marcaSelecionada.codigo)
  }
}

const fetchModelos = async (marcaId) => {
  try {
    const response = await fetch(`https://parallelum.com.br/fipe/api/v1/carros/marcas/${marcaId}/modelos`)
    const data = await response.json()
    modelos.value = data.modelos
  } catch (error) {
    console.error("Erro ao buscar modelos:", error)
  }
}

function aoDigitarPlaca(e) {
  form.placa = veiculoStore.formatarPlaca(e.target.value)
}

async function submeter() {
  erros.placa = '' // Limpa o erro antes de validar

  try {
    // Enviamos a placa e o ID atual do veículo
    const { data } = await axios.get('/api/veiculos/verificar-placa', { 
      params: { 
        placa: form.placa, 
        id: form.id 
      } 
    })

    if (data.existe) {
      erros.placa = 'Esta placa já pertence a outro veículo.'
      return // Interrompe a submissão se a placa estiver duplicada
    }

    // Se a placa for válida (ou for a mesma do veículo atual), salva
    emit('salvar', form)
  } catch (error) {
    console.error("Erro ao validar placa:", error)
  }
}

// function submeter() {
//   emit('salvar', form)
// }
</script>