<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Revisao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function todosVeiculos()
    {
        $veiculos = Veiculo::with('cliente')->get();
        return response()->json($veiculos);
    }

    public function veiculosPorPessoa()
    {
        $dados = Cliente::withCount('veiculos')
            ->orderBy('nome')
            ->get(['id', 'nome', 'veiculos_count']);
        return response()->json($dados);
    }

    public function topQuantVeiculos()
    {
        $dados = Cliente::selectRaw('genero, COUNT(veiculos.id) as total')
            ->leftJoin('veiculos', 'clientes.id', '=', 'veiculos.cliente_id')
            ->groupBy('genero')
            ->get();
        return response()->json($dados);
    }

    public function veiculosPorMarca()
    {
        $dados = Veiculo::selectRaw('marca, COUNT(*) as total')
            ->groupBy('marca')
            ->orderByDesc('total')
            ->get();
        return response()->json($dados);
    }

    public function marcasHomens()
    {
        $dados = Veiculo::selectRaw('marca, COUNT(*) as total')
            ->join('clientes', 'veiculos.cliente_id', '=', 'clientes.id')
            ->where('clientes.genero', 'M')
            ->groupBy('marca')
            ->orderByDesc('total')
            ->get();
        return response()->json($dados);
    }

    public function marcasMulheres()
    {
        $dados = Veiculo::selectRaw('marca, COUNT(*) as total')
            ->join('clientes', 'veiculos.cliente_id', '=', 'clientes.id')
            ->where('clientes.genero', 'F')
            ->groupBy('marca')
            ->orderByDesc('total')
            ->get();
        return response()->json($dados);
    }

    // ── Pessoas ──────────────────────────────────────
    public function todasPessoas()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return response()->json($clientes);
    }

    public function pessoasPorGenero()
    {
        $dados = Cliente::selectRaw("
            genero,
            COUNT(*) as total,
            AVG(DATE_PART('year', AGE(data_nascimento))) as idade_media
        ")
        ->groupBy('genero')
        ->get();
        return response()->json($dados);
    }

    // ── Revisões ─────────────────────────────────────
    public function revisoesPorPeriodo(Request $request)
    {
        $request->validate([
            'inicio' => 'required|date',
            'fim'    => 'required|date',
        ]);

        $dados = DB::select("
            SELECT
                TO_CHAR(data_revisao, 'DD/MM/YYYY') as data_formatada,
                COUNT(*) as total
            FROM revisoes
            WHERE data_revisao BETWEEN ? AND ?
            GROUP BY data_revisao
            ORDER BY data_revisao
        ", [$request->inicio, $request->fim]);

        return response()->json($dados);
    }

    public function marcasComMaisRevisoes()
    {
        $dados = Veiculo::selectRaw('marca, COUNT(revisoes.id) as total')
            ->join('revisoes', 'veiculos.id', '=', 'revisoes.veiculo_id')
            ->groupBy('marca')
            ->orderByDesc('total')
            ->get();
        return response()->json($dados);
    }

    public function pessoasComMaisRevisoes()
    {
        $dados = Cliente::selectRaw('clientes.id, clientes.nome, COUNT(revisoes.id) as total')
            ->join('veiculos', 'clientes.id', '=', 'veiculos.cliente_id')
            ->join('revisoes', 'veiculos.id', '=', 'revisoes.veiculo_id')
            ->groupBy('clientes.id', 'clientes.nome')
            ->orderByDesc('total')
            ->get();
        return response()->json($dados);
    }

    public function mediaTempoRevisoes()
    {
        $dados = DB::select("
            SELECT
                c.id,
                c.nome,
                AVG(diff_days) as media_dias
            FROM clientes c
            INNER JOIN veiculos v ON c.id = v.cliente_id
            INNER JOIN (
                SELECT
                    veiculo_id,
                    EXTRACT(EPOCH FROM (data_revisao::timestamp - LAG(data_revisao::timestamp)
                    OVER (PARTITION BY veiculo_id ORDER BY data_revisao))) / 86400 as diff_days
                FROM revisoes
            ) diffs ON v.id = diffs.veiculo_id
            GROUP BY c.id, c.nome
            ORDER BY c.nome
        ");

        return response()->json($dados);
    }

    public function proximasRevisoes()
    {
        $dados = DB::select("
            SELECT
                c.id,
                c.nome,
                v.id as veiculo_id,
                v.marca,
                v.modelo,
                MAX(r.data_revisao) as ultima_revisao,
                AVG(diff_days) as media_dias
            FROM clientes c
            INNER JOIN veiculos v ON c.id = v.cliente_id
            INNER JOIN (
                SELECT
                    veiculo_id,
                    data_revisao,
                    EXTRACT(EPOCH FROM (
                        data_revisao::timestamp - LAG(data_revisao::timestamp)
                        OVER (PARTITION BY veiculo_id ORDER BY data_revisao)
                    )) / 86400 as diff_days
                FROM revisoes
            ) r ON v.id = r.veiculo_id
            GROUP BY c.id, c.nome, v.id, v.marca, v.modelo
        ");

        $dados = collect($dados)->map(function($item) {
            $item->proxima_revisao = $item->media_dias
                ? date('Y-m-d', strtotime($item->ultima_revisao . ' + ' . round($item->media_dias) . ' days'))
                : null;
            return $item;
        });

        return response()->json($dados);
    }
}
