<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revisao extends Model
{
    /** @use HasFactory<\Database\Factories\RevisaoFactory> */
    use HasFactory;

    protected $table = 'revisoes';
    protected $fillable = [
        'veiculo_id',
        'data_revisao',
        'km_revisao',
        'preco_total',
        'descricao',
    ];
    protected function casts(): array
    {
        return [
            'data_revisao' => 'date', // vira Carbon automaticamente
            'km_revisao' => 'integer',
            'preco_total' => 'decimal:2', // sempre 2 casas decimais
        ];
    }

    // ── Relacionamentos ──────────────────────────────
    /** * Revisão pertence a um veículo */
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    /** * Acesso direto ao dono do veículo (através de Veiculo) * Uso: $revisao->user */
    public function user()
    {
        return $this->hasOneThrough( User::class, Veiculo::class, 'id');
    }

    // ── Scopes ───────────────────────────────────────
    /** * Revisões de um período * Uso: Revisao::doPeriodo('2024-01-01', '2024-12-31')->get() */
    public function scopeDoPeriodo($query, string $inicio, string $fim)
    {
        return $query->whereBetween('data_revisao', [$inicio, $fim]);
    }

    /** * Revisões acima de um valor * Uso: Revisao::acimaDe(500)->get() */
    public function scopeAcimaDe($query, float $valor)
    {
        return $query->where('preco_total', '>=', $valor);
    }
}
