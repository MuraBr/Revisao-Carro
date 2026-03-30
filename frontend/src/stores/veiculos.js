import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '../axios'

export const useVeiculoStore = defineStore('veiculo', () => {
  const veiculos = ref([])
  const carregando = ref(true)

  const total = ref(0)
  const paginaAtual = ref(1)
  const ultimaPagina = ref(1)

  // async function buscarTodos() {
  //   try {
  //     const { data } = await axios.get('/api/veiculos')
  //     veiculos.value = data
  //   } catch (e) {
  //     console.error(e)
  //   } finally {
  //     carregando.value = false
  //   }
  // }

  async function buscarTodos(page = 1, search = '') {
    carregando.value = true
    try {
      const { data } = await axios.get('/api/veiculos', {
        params: { page, search }
      })
      veiculos.value = data.data
      total.value = data.total
      paginaAtual.value = data.current_page
      ultimaPagina.value = data.last_page
    } catch (e) {
      console.error(e)
    } finally {
      carregando.value = false
    }
  }

  // async function buscarDoCliente(clienteId) {
  //   try {
  //     const { data } = await axios.get(`/api/clientes/${clienteId}/veiculos`)
  //   veiculos.value = data
  //   } catch (e) {
  //     console.error(e)
  //   } finally {
  //     carregando.value = false
  //   }
  // }

  async function buscarDoCliente(clienteId, page = 1, search = '') {
    veiculos.value = []
    carregando.value = true
    try {
      const { data } = await axios.get(`/api/clientes/${clienteId}/veiculos`, {
        params: { page, search }
      })
      veiculos.value = data.data
      total.value = data.total
      paginaAtual.value = data.current_page
      ultimaPagina.value = data.last_page
    } catch (e) {
      console.error(e)
    } finally {
      carregando.value = false
    }
  }

  async function criar(clienteId, form) {
    const { data } = await axios.post(`/api/clientes/${clienteId}/veiculos`, form)
    veiculos.value.push(data)
  }

  async function atualizar(clienteId, veiculoId, form) {
    const { data } = await axios.put(`/api/clientes/${clienteId}/veiculos/${veiculoId}`, form)
    const index = veiculos.value.findIndex(v => v.id === veiculoId)
    veiculos.value[index] = data
  }

  async function excluir(clienteId, veiculoId) {
    await axios.delete(`/api/clientes/${clienteId}/veiculos/${veiculoId}`)
    veiculos.value = veiculos.value.filter(v => v.id !== veiculoId)
  }

  function formatarPlaca(valor) {
    return valor.toString()
      .replace(/[^a-zA-Z0-9]/g, '')
      .toUpperCase()
      .slice(0, 7);
  }

  return {
    veiculos, carregando, buscarTodos,
    buscarDoCliente, criar, atualizar,
    excluir, total, paginaAtual,
    ultimaPagina, formatarPlaca
  }
})