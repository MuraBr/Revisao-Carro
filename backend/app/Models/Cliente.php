<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'telefone',
        'endereco',
        'numero',
        'cep',
        'data_nascimento',
        'genero',
    ];

    // relacionamento com veículos
    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }
}
