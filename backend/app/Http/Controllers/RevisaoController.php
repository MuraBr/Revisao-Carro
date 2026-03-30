<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Models\Cliente;
use App\Models\Revisao;
use Illuminate\Http\Request;

class RevisaoController extends Controller
{
    // public function todas()
    // {
    //     $revisoes = Revisao::with('veiculo.cliente')->get();
    //     return response()->json($revisoes);
    // }

    public function todas(Request $request)
    {
        $query = Revisao::with('veiculo.cliente')->orderBy('data_revisao', 'desc');

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('descricao', 'like', '%' . $request->search . '%')
                ->orWhere('preco_total', 'like', '%' . $request->search . '%')
                ->orWhere('km_revisao', 'like', '%' . $request->search . '%')
                ->orWhere('data_revisao', 'like', '%' . $request->search . '%');
            });
        }

        return $query->paginate(8);
    }

    // public function index(Cliente $cliente, Veiculo $veiculo)
    // {
    //     if ($veiculo->cliente_id !== $cliente->id) {
    //         return response()->json(['message' => 'Não autorizado.'], 403);
    //     }
    //     $response = $veiculo->revisoes()->get();
    //     return response()->json($response);
    // }

    public function index(Cliente $cliente, Veiculo $veiculo, Request $request)
    {
        $query = $veiculo->revisoes()->orderBy('data_revisao', 'desc');

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('descricao', 'like', '%' . $request->search . '%')
                ->orWhere('preco_total', 'like', '%' . $request->search . '%')
                ->orWhere('km_revisao', 'like', '%' . $request->search . '%')
                ->orWhere('data_revisao', 'like', '%' . $request->search . '%');
            });
        }

        return $query->paginate(8);
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
