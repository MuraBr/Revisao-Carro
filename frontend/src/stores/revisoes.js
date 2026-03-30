import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '../axios'

export const useRevisaoStore = defineStore('revisao', () => {
  const revisoes = ref([])
  const carregando = ref(true)

  const total = ref(0)
  const paginaAtual = ref(1)
  const ultimaPagina = ref(1)

  function limparParaBanco(form) {
    return {
      ...form,
      // Transforma "1.500,50" em 1500.50
      preco_total: parseFloat(form.preco_total.toString().replace(/\./g, '').replace(',', '.')),
      // Transforma "999.999" em 999999
      km_revisao: parseInt(form.km_revisao.toString().replace(/\D/g, ''))
    }
  }
  
  // async function buscarTodas() {
  //   revisoes.value = []
  //   carregando.value = true
  //   try {
  //     const { data } = await axios.get('/api/revisoes')
  //     revisoes.value = data
  //   } catch (e) {
  //     console.log(e)
  //   } finally {
  //     carregando.value = false
  //   }
  // }

  async function buscarTodas(page = 1, search = '') {
    carregando.value = true
    try {
      const { data } = await axios.get('/api/revisoes', {
        params: { page, search }
      })
      revisoes.value = data.data
      total.value = data.total
      paginaAtual.value = data.current_page
      ultimaPagina.value = data.last_page
    } catch (e) {
      console.error(e)
    } finally {
      carregando.value = false
    }
  }

  // async function buscarDoVeiculo(clienteId, veiculoId) {
  //   revisoes.value = []      // ← limpa as revisões anteriores
  //   carregando.value = true  // ← volta a carregar
  //   try {
  //     const { data } = await axios.get(`/api/clientes/${clienteId}/veiculos/${veiculoId}/revisoes`)
  //     revisoes.value = data
  //   } catch (e) {
  //     console.log(e)
  //   } finally {
  //     carregando.value = false
  //   }
  // }

  async function buscarDoVeiculo(clienteId, veiculoId, page = 1, search = '') {
    revisoes.value = []
    carregando.value = true
    try {
      const { data } = await axios.get(`/api/clientes/${clienteId}/veiculos/${veiculoId}/revisoes`, {
        params: { page, search }
      })
      revisoes.value = data.data
      total.value = data.total
      paginaAtual.value = data.current_page
      ultimaPagina.value = data.last_page
    } catch (e) {
      console.error(e)
    } finally {
      carregando.value = false
    }
  }

  async function criar(clienteId, veiculoId, form) {
    const dadosLimpos = limparParaBanco(form)
    const { data } = await axios.post(`/api/clientes/${clienteId}/veiculos/${veiculoId}/revisoes`, dadosLimpos)
    revisoes.value.push(data)
  }

  async function atualizar(clienteId, veiculoId, id, form) {
    const dadosLimpos = limparParaBanco(form)
    const { data } = await axios.put(
      `/api/clientes/${clienteId}/veiculos/${veiculoId}/revisoes/${id}`,
      dadosLimpos
    )
    const index = revisoes.value.findIndex(r => r.id === id)
    revisoes.value[index] = data
  }

  // async function criar(clienteId, veiculoId, form) {
  //   const { data } = await axios.post(`/api/clientes/${clienteId}/veiculos/${veiculoId}/revisoes`, form)
  //   revisoes.value.push(data)
  // }

  // async function atualizar(clienteId, veiculoId, id, form) {
  //   const { data } = await axios.put(
  //     `/api/clientes/${clienteId}/veiculos/${veiculoId}/revisoes/${id}`,
  //     form
  //   )
  //   const index = revisoes.value.findIndex(r => r.id === id)
  //   revisoes.value[index] = data
  // }

  async function excluir(clienteId, veiculoId, id) {
    await axios.delete(`/api/clientes/${clienteId}/veiculos/${veiculoId}/revisoes/${id}`)
    revisoes.value = revisoes.value.filter(r => r.id !== id)
  }

  function formatarPreco(valor) {

    let limpo = valor.toString().replace(/\D/g, '');

    let numero = (parseInt(limpo) || 0).toString();

    numero = numero.padStart(3, '0');

    let reais = numero.slice(0, -2);
    let centavos = numero.slice(-2);

    reais = reais.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    if (numero.length > 8) {
      return this.formatarPreco(numero.slice(0, 8));
    }

    return `${reais},${centavos}`;
  } 

  function formatarKm(valor) {
    let limpo = valor.toString().replace(/\D/g, '');
    
    limpo = limpo.slice(0, 9);
    
    return limpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  return {
    revisoes, carregando, buscarTodas,
    buscarDoVeiculo, criar, atualizar,
    excluir, total, paginaAtual,
    ultimaPagina, formatarPreco, formatarKm
  }
})