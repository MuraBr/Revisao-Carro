<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Cache;

class VeiculoController extends Controller
{
    // public function todos()
    // {
    //     $veiculos = Veiculo::with('cliente')->get();
    //     return response()->json($veiculos);
    // }

    // public function todos(Request $request)
    // {
    //     $query = Veiculo::with('cliente')->orderBy('marca');

    //     if ($request->has('search') && $request->search) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('marca', 'ilike', '%' . $request->search . '%')
    //             ->orWhere('modelo', 'ilike', '%' . $request->search . '%');
    //         });
    //     }

    //     return $query->paginate(8);
    // }

    public function todos(Request $request)
    {
        $search = $request->query('search', '');
        $page = $request->query('page', 1);

        // Chave específica para a listagem GERAL (sem ID de cliente)
        $cacheKey = "veiculos_todos_geral_p{$page}_s_" . md5((string)$search);

        return Cache::tags(['veiculos'])->remember($cacheKey, 3600, function () use ($search) {
            // Busca todos os veículos com seus respectivos donos (clientes)
            $query = Veiculo::with('cliente')->orderBy('marca');

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('marca', 'ilike', '%' . $search . '%')
                    ->orWhere('modelo', 'ilike', '%' . $search . '%')
                    ->orWhere('placa', 'ilike', '%' . $search . '%');
                });
            }

            // Retorna toArray() para evitar erro de serialização no Predis
            return $query->paginate(8)->toArray();
        });
    }

    // public function index(Cliente $cliente)
    // {
    //     $veiculos = Veiculo::where('cliente_id', $cliente->id)->get();
    //     return response()->json($veiculos);
    // }

    // public function index(Cliente $cliente, Request $request)
    // {
    //     $query = $cliente->veiculos()->orderBy('marca');

    //     if ($request->has('search') && $request->search) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('marca', 'ilike', '%' . $request->search . '%')
    //             ->orWhere('modelo', 'ilike', '%' . $request->search . '%');
    //         });
    //     }

    //     return $query->paginate(8);
    // }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $page = $request->query('page', 1);
        $clienteId = $request->query('cliente_id'); // Captura o ID do cliente se houver

        // A chave agora inclui o clienteId para diferenciar "Meus Veículos" de "Todos os Veículos"
        $cacheKey = "veiculos_index_c{$clienteId}_p{$page}_s_" . md5((string)$search);

        return Cache::tags(['veiculos'])->remember($cacheKey, 3600, function () use ($search, $clienteId) {
            $query = Veiculo::with('cliente')->orderBy('placa', 'asc');

            // Se houver cliente_id, filtra apenas os veículos dele
            if ($clienteId) {
                $query->where('cliente_id', $clienteId);
            }

            // Se houver termo de busca, filtra por placa ou modelo
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('placa', 'ilike', '%' . $search . '%')
                    ->orWhere('modelo', 'ilike', '%' . $search . '%');
                });
            }

            return $query->paginate(8)->toArray();
        });
    }

    public function store(Cliente $cliente, Request $request)
    {
        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        $request->validate([
            'placa' => 'required|string|unique:veiculos,placa',
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'cor' => 'required|string',
            'ano' => 'required|integer',
        ]);

        $veiculo = $cliente->veiculos()->create([
            'placa' => $request->placa,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'cor' => $request->cor,
            'ano' => $request->ano,
        ]);

        return response()->json($veiculo, 201);
    }

    public function show(Veiculo $veiculo, Cliente $cliente)
    {
        if ($cliente->id !== $veiculo->cliente_id)
        {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        return response()->json($veiculo);
    }

    public function update(Request $request, Cliente $cliente, Veiculo $veiculo)
    {
        if ($cliente->id !== $veiculo->cliente_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $request->validate([
            'placa' => 'sometimes|string|unique:veiculos,placa,' . $veiculo->id,
            'marca' => 'sometimes|string',
            'modelo' => 'sometimes|string',
            'cor' => 'sometimes|string',
            'ano' => 'sometimes|integer|min:1900|max:2100',
        ]);

        $veiculo->update($request->only(['placa', 'marca', 'modelo', 'cor', 'ano']));

        return response()->json($veiculo->load('cliente'));
    }

    public function destroy(Cliente $cliente, Veiculo $veiculo)
    {
        if ($cliente->id !== $veiculo->cliente_id)
        {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $veiculo->delete();
        return response()->json(['message' => 'Veículo excluído com sucesso.'], 204);
    }

    public function verificarPlaca(Request $request)
    {
        $existe = Veiculo::where('placa', $request->placa)
            ->when($request->id, function ($q) use ($request) {
                // Se houver um ID, ignora o veículo que estamos editando
                return $q->where('id', '!=', $request->id);
            })
            ->exists();

        return response()->json(['existe' => $existe]);
    }
}
