<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;

class Veiculo extends Model
{
    /** @use HasFactory<\Database\Factories\VeiculoFactory> */
    use HasFactory;

    /** * Laravel infere a tabela como "veiculos" automaticamente * (plural snake_case do nome da classe) */
    protected $fillable = [
        'cliente_id',
        'placa',
        'marca',
        'modelo',
        'cor',
        'ano',
    ];

    protected function casts(): array
    {
        return [ 'ano' => 'integer', ];
    }

    // ── Relacionamentos ──────────────────────────────
    /** * Veículo pertence a um usuário */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /** * Veículo tem muitas revisões */
    public function revisoes()
    {
        return $this->hasMany(Revisao::class);
    }

    // ── Scopes ───────────────────────────────────────
    /** * Filtrar por marca * Uso: Veiculo::daMarca('Toyota')->get() */
    public function scopeDaMarca($query, string $marca)
    {
        return $query->where('marca', $marca);
    }

    /** * Filtrar veículos com pelo menos uma revisão * Uso: Veiculo::comRevisoes()->get() */
    public function scopeComRevisoes($query)
    {
        return $query->has('revisoes');
    }

    // ── Helpers ──────────────────────────────────────
    /** * Retorna a última revisão do veículo */
    public function ultimaRevisao()
    {
        return $this->revisoes() ->latest('data_revisao') ->first();
    }
}
