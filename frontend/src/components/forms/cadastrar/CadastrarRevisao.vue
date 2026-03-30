<template>
  <div class="cadastrar-revisao">
    <form @submit.prevent="submeter">
      <div class="form-group">
        <label for="data_revisao">Data da Revisão:</label>
        <input type="date" id="data_revisao" v-model="form.data_revisao" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label for="km_revisao">Quilometragem:</label>
        <input
          type="text" 
          id="km_revisao"
          :value="form.km_revisao"
          @input="aoDigitarKm"
          @keyup="aoDigitarKm"
          maxlength="11"
          placeholder="0"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label for="preco_total">Preço Total:</label>
        <input
          type="text" 
          id="preco_total"
          :value="form.preco_total"
          @input="aoDigitarPreco"
          @keyup="aoDigitarPreco"
          maxlength="10"
          placeholder="0,00"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="form-group">
        <label for="descricao">Descrição:</label>
        <input type="text" id="descricao" v-model="form.descricao"
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
import { reactive } from 'vue';
import { useRevisaoStore } from '../../../stores/revisoes.js';

const revisaoStore = useRevisaoStore()

const emit = defineEmits(['salvar', 'cancelar']);

const form = reactive({
  data_revisao: '',
  km_revisao: '', 
  preco_total: '',
  descricao: '',
});

function aoDigitarPreco(e) {
  form.preco_total = revisaoStore.formatarPreco(e.target.value);
}

function aoDigitarKm(e) {
  form.km_revisao = revisaoStore.formatarKm(e.target.value);
}

function submeter() {
  emit('salvar', form)  // envia os dados para a view pai
}
</script>