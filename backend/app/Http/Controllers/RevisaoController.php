<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Cliente;
use App\Models\Revisao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RevisaoController extends Controller
{
    // public function todas()
    // {
    //     $revisoes = Revisao::with('veiculo.cliente')->get();
    //     return response()->json($revisoes);
    // }

    // public function todas(Request $request)
    // {
    //     $query = Revisao::with('veiculo.cliente')->orderBy('data_revisao', 'desc');

    //     if ($request->has('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('descricao', 'like', '%' . $request->search . '%')
    //             ->orWhere('preco_total', 'like', '%' . $request->search . '%')
    //             ->orWhere('km_revisao', 'like', '%' . $request->search . '%')
    //             ->orWhere('data_revisao', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     return $query->paginate(8);
    // }

    public function todas(Request $request)
    {
        $search = $request->query('search', '');
        $page = $request->query('page', 1);

        // Chave única para a listagem ADMINISTRATIVA (todas as revisões)
        $cacheKey = "revisoes_todas_geral_p{$page}_s_" . md5((string)$search);

        return Cache::tags(['revisoes'])->remember($cacheKey, 3600, function () use ($search) {
            // Carrega veículo e o dono do veículo (cliente) em uma única consulta (Eager Loading)
            $query = Revisao::with('veiculo.cliente')->orderBy('data_revisao', 'desc');

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    // Usamos 'ilike' para o PostgreSQL ser case-insensitive
                    $q->where('descricao', 'ilike', '%' . $search . '%')
                    ->orWhere('preco_total', 'ilike', '%' . $search . '%')
                    ->orWhere('km_revisao', 'ilike', '%' . $search . '%')
                    ->orWhere('data_revisao', 'ilike', '%' . $search . '%');
                });
            }

            // Converte para Array para que o Predis consiga salvar sem erro de 'Closure'
            return $query->paginate(8)->toArray();
        });
    }

    // public function index(Cliente $cliente, Veiculo $veiculo)
    // {
    //     if ($veiculo->cliente_id !== $cliente->id) {
    //         return response()->json(['message' => 'Não autorizado.'], 403);
    //     }
    //     $response = $veiculo->revisoes()->get();
    //     return response()->json($response);
    // }

    // public function index(Cliente $cliente, Veiculo $veiculo, Request $request)
    // {
    //     $query = $veiculo->revisoes()->orderBy('data_revisao', 'desc');

    //     if ($request->has('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('descricao', 'like', '%' . $request->search . '%')
    //             ->orWhere('preco_total', 'like', '%' . $request->search . '%')
    //             ->orWhere('km_revisao', 'like', '%' . $request->search . '%')
    //             ->orWhere('data_revisao', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     return $query->paginate(8);
    // }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $page = $request->query('page', 1);
        $clienteId = $request->query('cliente_id');

        $cacheKey = "revisoes_index_c{$clienteId}_p{$page}_s_" . md5((string)$search);

        return Cache::tags(['revisoes'])->remember($cacheKey, 3600, function () use ($search, $clienteId) {
            $query = Revisao::with(['cliente', 'veiculo'])->orderBy('data_revisao', 'desc');

            if ($clienteId) {
                $query->where('cliente_id', $clienteId);
            }

            if (!empty($search)) {
                $query->whereHas('veiculo', function($q) use ($search) {
                    $q->where('placa', 'ilike', '%' . $search . '%');
                });
            }

            return $query->paginate(8)->toArray();
        });
    }

    public function store(Request $request, Cliente $cliente,  Veiculo $veiculo)
    {
        if ($veiculo->cliente_id !== $cliente->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }
        $request->validate([
            'data_revisao' => 'required|date',
            'km_revisao' => 'required|integer|min:0',
            'preco_total' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        $revisao = $veiculo->revisoes()->create([
            'data_revisao' => $request->data_revisao,
            'km_revisao' => $request->km_revisao,
            'preco_total' => $request->preco_total,
            'descricao' => $request->descricao,
        ]);
        return response()->json($revisao, 201);
    }

    public function update(Request $request, Cliente $cliente, Veiculo $veiculo, $id)
    {
        if ($veiculo->cliente_id !== $cliente->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $revisao = $veiculo->revisoes()->find($id);

        if (!$revisao) {
            return response()->json(['message' => 'Revisão não encontrada.'], 404);
        }

        $request->validate([
            'data_revisao' => 'sometimes|date',
            'km_revisao'   => 'sometimes|integer|min:0',
            'preco_total'  => 'sometimes|numeric|min:0',
            'descricao'    => 'nullable|string',
        ]);

        $revisao->update($request->only(['data_revisao', 'km_revisao', 'preco_total', 'descricao']));

        return response()->json($revisao->load('veiculo.cliente'));
    }

    public function show(Cliente $cliente, Veiculo $veiculo, $id)
    {
        if ($veiculo->cliente_id !== $cliente->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }
        $revisao = $veiculo->revisoes()->find($id);
        if (!$revisao)
        {
            return response()->json(['message' => 'Revisão não encontrada.'], 404);
        }
        return response()->json($revisao);
    }

    public function destroy(Cliente $cliente, Veiculo $veiculo, $id)
    {
        if ($veiculo->cliente_id !== $cliente->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $revisao = $veiculo->revisoes()->find($id);
        if (!$revisao)
        {
            return response()->json(['message' => 'Revisão não encontrada.'], 404);
        }

        $revisao->delete();
        return response()->json(['message' => 'Revisão deletada.']);
    }
}
