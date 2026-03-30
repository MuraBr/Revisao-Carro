import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '../axios'

export const useClienteStore = defineStore('cliente', () => {
  const clientes = ref([])
  const carregando = ref(true)

  const total = ref(0)
  const paginaAtual = ref(1)
  const ultimaPagina = ref(1)

  // async function buscarTodos() {
  //   try {
  //     const { data } = await axios.get('/api/clientes')
  //     clientes.value = data
  //   } catch (e) {
  //     console.error(e)
  //   } finally {
  //     carregando.value = false  // sempre vira false, mesmo com erro
  //   }
  // }

  async function buscarTodos(page = 1, search = '') {
    carregando.value = true
    try {
      const { data } = await axios.get('/api/clientes', {
        params: { page, search }
      })
      clientes.value = data.data
      total.value = data.total
      paginaAtual.value = data.current_page
      ultimaPagina.value = data.last_page
    } catch (e) {
      console.error(e)
    } finally {
      carregando.value = false
    }
  }

  async function criar(form) {
    const { data } = await axios.post('/api/clientes', form)
    clientes.value.push(data)
  }

  async function excluir(id) {
    await axios.delete(`/api/clientes/${id}`)
    clientes.value = clientes.value.filter(c => c.id !== id)
  }

  async function atualizar(id, form) {
    const { data } = await axios.put(`/api/clientes/${id}`, form)
    const index = clientes.value.findIndex(c => c.id === id)
    clientes.value[index] = data
  }

  async function buscarCep(cep) {
    const cepLimpo = cep.replace(/\D/g, '')
    if (cepLimpo.length !== 8) return null

    try {
      const res = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
      const dados = await res.json()
      if (!dados.erro) {
        return {
          endereco: `${dados.logradouro}, ${dados.bairro}, ${dados.localidade} - ${dados.uf}`,
          cep: dados.cep
        }
      }
    } catch (e) {
      console.error('CEP não encontrado')
      return null
    }
  }

  function formatarCpf(valor) {
    return valor
      .replace(/\D/g, '')
      .slice(0, 11)
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
  }

  function formatarCep(valor) {
    return valor
      .replace(/\D/g, '')
      .slice(0, 8)
      .replace(/(\d{5})(\d)/, '$1-$2')
  }

  function formatarTelefone(valor) {
    const ret = valor.toString().replace(/\D/g, '')
    
    return ret
      .replace(/(\d{2})(\d)/, '($1) $2')
      .replace(/(\d{5})(\d)/, '$1-$2')
      .replace(/(-\d{4})\d+?$/, '$1') // Limita o tamanho máximo
  }

  return {
    clientes, carregando, total,
    paginaAtual, ultimaPagina, buscarTodos,
    criar, excluir, atualizar, buscarCep,
    formatarCpf, formatarCep, formatarTelefone
  }
})