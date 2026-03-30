<template>
  <AppLayout titulo="Dashboard">
    <div class="px-2 flex flex-col gap-6">

      <!-- Cards de métricas -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Total de clientes</p>
          <p class="text-3xl font-bold text-blue-900 mt-1">{{ totalClientes }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Total de veículos</p>
          <p class="text-3xl font-bold text-blue-900 mt-1">{{ totalVeiculos }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Total de revisões</p>
          <p class="text-3xl font-bold text-blue-900 mt-1">{{ totalRevisoes }}</p>
        </div>
      </div>

      <!-- Gráficos linha 1 -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <!-- Pizza: homens vs mulheres -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:col-span-1">
          <h3 class="font-bold text-gray-800 mb-4">Veículos por gênero</h3>
          <Pie v-if="pizzaGenero.labels.length" :data="pizzaGenero" :options="optionsPizza" />
          <p v-else class="text-gray-400 text-sm text-center py-8">Sem dados</p>
        </div>

        <!-- Barras: veículos por marca -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:col-span-2">
          <h3 class="font-bold text-gray-800 mb-4">Veículos por marca</h3>
          <Bar v-if="barrasMarca.labels.length" :data="barrasMarca" :options="optionsBar" />
          <p v-else class="text-gray-400 text-sm text-center py-8">Sem dados</p>
        </div>
      </div>

      <!-- Gráficos linha 2 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Barras: pessoas com mais veículos -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-bold text-gray-800 mb-4">Pessoas com mais veículos</h3>
          <Bar v-if="barrasPessoas.labels.length" :data="barrasPessoas" :options="optionsBar" />
          <p v-else class="text-gray-400 text-sm text-center py-8">Sem dados</p>
        </div>

        <!-- Barras: marcas com mais revisões -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-bold text-gray-800 mb-4">Marcas com mais revisões</h3>
          <Bar v-if="barrasMarcasRevisoes.labels.length" :data="barrasMarcasRevisoes" :options="optionsBar" />
          <p v-else class="text-gray-400 text-sm text-center py-8">Sem dados</p>
        </div>
      </div>

      <!-- Gráficos linha 3 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Pessoas com mais revisões -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-bold text-gray-800 mb-4">Pessoas com mais revisões (top 10)</h3>
          <Bar v-if="barrasPessoasRevisoes.labels.length" :data="barrasPessoasRevisoes" :options="optionsBar" />
          <p v-else class="text-gray-400 text-sm text-center py-8">Sem dados</p>
        </div>

        <!-- Média de tempo entre revisões -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h3 class="font-bold text-gray-800 mb-4">Média de dias entre revisões</h3>
          <Bar v-if="barrasMediaTempo.labels.length" :data="barrasMediaTempo" :options="optionsBar" />
          <p v-else class="text-gray-400 text-sm text-center py-8">Sem dados</p>
        </div>
      </div>

      <!-- Gráfico linha 4: revisões por período -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-bold text-gray-800">Revisões por período</h3>
          <div class="flex items-center gap-2">
            <input v-model="periodoInicio" type="date"
              class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <span class="text-gray-400 text-sm">até</span>
            <input v-model="periodoFim" type="date"
              class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <button @click="buscarRevisoesPeriodo"
              class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700 transition">
              Buscar
            </button>
          </div>
        </div>
        <Line v-if="linhaRevisoes.labels.length" :data="linhaRevisoes" :options="optionsLine" />
        <p v-else class="text-gray-400 text-sm text-center py-8">Selecione um período</p>
      </div>

      <!-- Próximas revisões -->
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-bold text-gray-800 mb-4">Próximas revisões previstas</h3>
        <div class="flex flex-col gap-3">
          <div v-for="p in proximasRevisoes" :key="p.veiculo_id"
            class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
              <p class="font-medium text-gray-800">{{ p.marca }} {{ p.modelo }}</p>
              <p class="text-sm text-gray-500">{{ p.nome }}</p>
            </div>
            <span class="text-sm font-medium text-blue-700 bg-blue-50 px-3 py-1 rounded-full">
              {{ p.proxima_revisao ? formatarData(p.proxima_revisao) : 'Sem previsão' }}
            </span>
          </div>
          <p v-if="proximasRevisoes.length === 0" class="text-gray-400 text-sm text-center py-4">
            Sem previsões disponíveis
          </p>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Bar, Pie, Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import AppLayout from '../components/AppLayout.vue'
import axios from '../axios'
import { formatarData } from '../utils/formatadores'
import ChartDataLabels from 'chartjs-plugin-datalabels'

ChartJS.register(
  CategoryScale, LinearScale, BarElement,
  LineElement, PointElement, ArcElement,
  Title, Tooltip, Legend,
  ChartDataLabels  // ← adicione
)

const totalClientes   = ref(0)
const totalVeiculos   = ref(0)
const totalRevisoes   = ref(0)
const proximasRevisoes = ref([])
const periodoInicio   = ref('')
const periodoFim      = ref('')

const pizzaGenero = ref({ labels: [], datasets: [] })
const barrasMarca = ref({ labels: [], datasets: [] })
const barrasPessoas = ref({ labels: [], datasets: [] })
const barrasMarcasRevisoes = ref({ labels: [], datasets: [] })
const linhaRevisoes = ref({ labels: [], datasets: [] })
const barrasPessoasRevisoes = ref({ labels: [], datasets: [] })
const barrasMediaTempo = ref({ labels: [], datasets: [] })

const optionsPizza = {
  responsive: true,
  maintainAspectRatio: true,
  aspectRatio: 1.5,
  plugins: {
    legend: { position: 'bottom' },
    datalabels: {
      color: '#fff',
      font: { weight: 'bold', size: 13 },
      // Alteramos o formatter para retornar o valor original
      formatter: (value) => {
        return value; // Retorna o número de veículos (ex: 10, 25, 50)
      }
    }
  }
}

const optionsBar = {
  responsive: true,
  maintainAspectRatio: true,
  aspectRatio: 2.5,
  plugins: {
    legend: { display: false },
    datalabels: {
      anchor: 'end',
      align: 'top',
      color: '#1e40af',
      font: { weight: 'bold', size: 12 }
    }
  },
  scales: {
    y: {
      ticks: { stepSize: 1 },
      grace: '10%'
    }
  }
}

const optionsLine = {
  responsive: true,
  maintainAspectRatio: true,
  aspectRatio: 3,
  plugins: {
    legend: { display: false },
    datalabels: {
      // Define onde o label se prende (no final/topo do ponto)
      anchor: 'end', 
      // Define para qual lado o label "estica" em relação ao anchor
      align: 'top',
      // Opcional: adiciona um respiro (distância) em pixels
      offset: 4, 
      // Garante que o número apareça
      display: true,
      font: {
        weight: 'bold',
        size: 12
      }
    }
  },
  scales: {
    y: {
      ticks: {
        stepSize: 1,
        callback: value => Number.isInteger(value) ? value : null
      },
      min: 0,
      // Opcional: aumenta um pouco o topo do gráfico para o número não cortar
      grace: '10%' 
    }
  }
}

onMounted(async () => {
  // Métricas
  const [clientes, veiculos, revisoes] = await Promise.all([
    axios.get('/api/clientes'),
    axios.get('/api/veiculos'),
    axios.get('/api/revisoes'),
  ])
  totalClientes.value = clientes.data.total ?? clientes.data.length
  totalVeiculos.value = veiculos.data.total ?? veiculos.data.length
  totalRevisoes.value = revisoes.data.total ?? revisoes.data.length

  // Gráfico: homens vs mulheres
  const genero = await axios.get('/api/relatorios/veiculos-por-genero')
  pizzaGenero.value = {
    labels: genero.data.map(g => g.genero === 'M' ? 'Masculino' : 'Feminino'),
    datasets: [{
      data: genero.data.map(g => g.total),
      backgroundColor: ['#3B82F6', '#EC4899'],
    }]
  }

  // Gráfico: veículos por marca
  const marcas = await axios.get('/api/relatorios/veiculos-por-marca')
  barrasMarca.value = {
    labels: marcas.data.map(m => m.marca),
    datasets: [{
      data: marcas.data.map(m => m.total),
      backgroundColor: '#3B82F6',
    }]
  }

  // Gráfico: pessoas com mais veículos
  const pessoas = await axios.get('/api/relatorios/veiculos-por-pessoa')
  barrasPessoas.value = {
    labels: pessoas.data.map(p => p.nome),
    datasets: [{
      data: pessoas.data.map(p => p.veiculos_count),
      backgroundColor: '#8B5CF6',
    }]
  }

  // Gráfico: marcas com mais revisões
  const marcasRev = await axios.get('/api/relatorios/marcas-revisoes')
  barrasMarcasRevisoes.value = {
    labels: marcasRev.data.map(m => m.marca),
    datasets: [{
      data: marcasRev.data.map(m => m.total),
      backgroundColor: '#10B981',
    }]
  }

  // Próximas revisões
  const proximas = await axios.get('/api/relatorios/proximas-revisoes')
  proximasRevisoes.value = proximas.data

  const pessoasRev = await axios.get('/api/relatorios/pessoas-revisoes')
  barrasPessoasRevisoes.value = {
    labels: pessoasRev.data.map(p => p.nome),
    datasets: [{
      data: pessoasRev.data.map(p => p.total),
      backgroundColor: '#F59E0B',
    }]
  }

  const mediaTempo = await axios.get('/api/relatorios/media-tempo')
  barrasMediaTempo.value = {
    labels: mediaTempo.data.map(p => p.nome),
    datasets: [{
      label: 'Dias',
      data: mediaTempo.data.map(p => p.media_dias ? Math.round(p.media_dias) : 0),
      backgroundColor: '#6366F1',
    }]
  }
})

async function buscarRevisoesPeriodo() {
  if (!periodoInicio.value || !periodoFim.value) return
  const res = await axios.get('/api/relatorios/revisoes-por-periodo', {
    params: { inicio: periodoInicio.value, fim: periodoFim.value }
  })

  linhaRevisoes.value = {
    labels: res.data.map(r => r.data_formatada),
    datasets: [{
      label: 'Revisões',
      data: res.data.map(r => parseInt(r.total)),
      borderColor: '#3B82F6',
      backgroundColor: '#BFDBFE',
      tension: 0.3,
      fill: true,
    }]
  }
}
</script>