export function formatarData(data) {
  if (!data || data === '-') return '-'
  
  const dataLimpa = data.split('T')[0]; 
  const partes = dataLimpa.split('-'); 
  
  if (partes.length !== 3) return data;
  
  const [ano, mes, dia] = partes;

  return `${dia}/${mes}/${ano}`;
}

export function formatarPreco(valor) {
  if (!valor) return 'R$ 0,00'
  return new Number(valor).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  })
}